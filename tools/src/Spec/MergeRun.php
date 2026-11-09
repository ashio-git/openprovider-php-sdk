<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * The state of one merge. See SpecMerger for the policy.
 *
 * @internal
 */
final class MergeRun
{
    private const METHODS = ['get', 'put', 'post', 'delete', 'patch', 'head', 'options'];
    private const DEFINITIONS = '#/definitions/';
    private const SOURCE_MARK = 'x-openprovider-source';
    private const PORTAL_LABEL = 'the portal';

    /** @var array<string, array<array-key, mixed>> service => its definitions */
    private array $serviceDefinitions = [];

    /** @var array<array-key, mixed> */
    private array $portalDefinitions = [];

    /** @var array<string, array<string, true>> "service.name" => portal definitions describing the same schema */
    private array $pairs = [];

    /** @var array<string, array<string, array<string, true>>> portal name => service => service names */
    private array $portalToService = [];

    /** @var array<string, string> conflict id => detail, for fields, parameters, responses and security */
    private array $schemaConflicts = [];

    /** @var array<string, true> */
    private array $additions = [];

    /** @var array<string, true> */
    private array $enumUnions = [];

    public function __construct(private readonly Overrides $overrides)
    {
    }

    /**
     * @param array<array-key, mixed> $services
     * @param array<array-key, mixed> $sources
     */
    public function run(array $services, mixed $portalInput, array $sources): MergeResult
    {
        $portal = Json::object($portalInput, 'portal spec');
        $serviceOperations = $this->indexServices($services);
        $this->portalDefinitions = Json::object($portal['definitions'] ?? [], 'portal definitions');
        $portalOperations = self::operations($portal, self::PORTAL_LABEL);

        foreach ($portalOperations as $key => $portalOperation) {
            if (isset($serviceOperations[$key])) {
                [$service, $serviceOperation] = $serviceOperations[$key];
                $this->pairOperation($key, $service, $serviceOperation, $portalOperation);
            }
        }

        $operations = [];
        $portalOnly = [];
        foreach ($portalOperations as $key => $portalOperation) {
            if (isset($serviceOperations[$key])) {
                [$service, $serviceOperation] = $serviceOperations[$key];
                $operations[$key] = $this->sharedOperation($key, $service, $serviceOperation, $portalOperation);
            } else {
                $operations[$key] = $this->portalOperation($portalOperation);
                $portalOnly[] = $key;
            }
        }

        $nameConflicts = $this->applyOperationIds($operations);
        $definitions = $this->reachableDefinitions($operations);
        $this->rejectStaleOverrides();

        $conflicts = $nameConflicts;
        foreach ($this->schemaConflicts as $id => $detail) {
            $conflicts[$id] = new Conflict($id, $detail, $this->overrides->conflicts[$id] ?? null);
        }
        ksort($conflicts, SORT_STRING);
        $additions = array_keys($this->additions);
        sort($additions, SORT_STRING);
        $enumUnions = array_keys($this->enumUnions);
        sort($enumUnions, SORT_STRING);

        return new MergeResult(
            $this->assemble($portal, $sources, $operations, $definitions),
            $conflicts,
            $portalOnly,
            array_values(array_diff(array_keys($serviceOperations), array_keys($portalOperations))),
            $additions,
            $enumUnions,
        );
    }

    /**
     * @param array<array-key, mixed> $services
     *
     * @return array<string, array{string, array<array-key, mixed>}> "METHOD /path" => [service, operation]
     */
    private function indexServices(array $services): array
    {
        ksort($services, SORT_STRING);
        $operations = [];
        foreach (array_keys($services) as $key) {
            $name = (string) $key;
            if ($name === SpecMerger::PORTAL) {
                throw new SpecException('A service named "portal" collides with the namespace of portal-only definitions');
            }
            if (preg_match('/^[a-z0-9][a-z0-9-]*$/', $name) !== 1) {
                throw new SpecException(sprintf('Service name "%s" would make "<service>.<definition>" names ambiguous; use [a-z0-9-]', $name));
            }
            $spec = Json::object($services[$key], "service $name");
            $this->serviceDefinitions[$name] = Json::object($spec['definitions'] ?? [], "$name definitions");
            foreach (self::operations($spec, $name) as $key => $operation) {
                if (isset($operations[$key])) {
                    throw new SpecException(sprintf('%s is declared by both %s and %s', $key, $operations[$key][0], $name));
                }
                $operations[$key] = [$name, $operation];
            }
        }
        ksort($operations, SORT_STRING);

        return $operations;
    }

    /**
     * @param array<array-key, mixed> $spec
     *
     * @return array<string, array<array-key, mixed>> "METHOD /path" => operation, sorted
     */
    private static function operations(array $spec, string $origin): array
    {
        $operations = [];
        foreach (Json::objectMap($spec['paths'] ?? [], "$origin paths") as $path => $item) {
            $path = (string) $path;
            if (preg_match('#^/v1(?:beta)?(/.+)$#', $path, $match) !== 1) {
                throw new SpecException(sprintf('Path %s of %s does not start with /v1 or /v1beta', $path, $origin));
            }
            foreach (array_keys($item) as $method) {
                $method = (string) $method;
                if (str_starts_with($method, 'x-')) {
                    continue;
                }
                if (!in_array($method, self::METHODS, true)) {
                    throw new SpecException(sprintf('Path %s of %s has "%s", which the merge does not support', $path, $origin, $method));
                }
                $key = strtoupper($method) . ' ' . $match[1];
                if (isset($operations[$key])) {
                    throw new SpecException(sprintf('%s appears twice in %s', $key, $origin));
                }
                $operations[$key] = Json::object($item[$method], "$origin $key");
            }
        }
        ksort($operations, SORT_STRING);

        return $operations;
    }

    /**
     * @param array<array-key, mixed> $serviceOperation
     * @param array<array-key, mixed> $portalOperation
     */
    private function pairOperation(string $key, string $service, array $serviceOperation, array $portalOperation): void
    {
        $serviceParameters = self::parametersById($serviceOperation, $key);
        foreach (self::parametersById($portalOperation, $key) as $id => $portalParameter) {
            if (!isset($serviceParameters[$id])) {
                continue;
            }
            $serviceParameter = $serviceParameters[$id];
            $difference = isset($serviceParameter['schema']) || isset($portalParameter['schema'])
                ? $this->compare($service, $serviceParameter['schema'] ?? null, $portalParameter['schema'] ?? null)
                : self::compareParameters($serviceParameter, $portalParameter);
            if ($difference !== null) {
                $this->schemaConflict("paths/$key/parameters/$id", $difference);
            }
        }

        $serviceResponses = Json::object($serviceOperation['responses'] ?? [], "$key responses");
        foreach (Json::objectMap($portalOperation['responses'] ?? [], "$key responses") as $code => $portalResponse) {
            $serviceResponse = Json::object($serviceResponses[$code] ?? [], "$key response $code");
            if (isset($serviceResponse['schema'], $portalResponse['schema'])) {
                $difference = $this->compare($service, $serviceResponse['schema'], $portalResponse['schema']);
                if ($difference !== null) {
                    $this->schemaConflict("paths/$key/responses/$code", $difference);
                }
            }
        }

        if (($serviceOperation['security'] ?? null) != ($portalOperation['security'] ?? null)) {
            $this->schemaConflict("paths/$key/security", sprintf(
                'service %s vs portal %s',
                self::show($serviceOperation['security'] ?? null),
                self::show($portalOperation['security'] ?? null),
            ));
        }
    }

    /**
     * Null when both schemas describe the same shape; otherwise what differs. Pairs every
     * `$ref` it meets so that the referenced definitions get compared and united too.
     */
    private function compare(string $service, mixed $serviceSchema, mixed $portalSchema): ?string
    {
        $s = Json::object($serviceSchema, 'service schema');
        $p = Json::object($portalSchema, 'portal schema');
        $serviceRef = self::refName($s);
        $portalRef = self::refName($p);
        if ($serviceRef !== null && $portalRef !== null) {
            $this->pair($service, $serviceRef, $portalRef);

            return null;
        }
        if ($serviceRef !== null || $portalRef !== null) {
            return self::describe($s) . ' vs ' . self::describe($p);
        }
        if (self::typeOf($s) !== self::typeOf($p)) {
            return self::typeOf($s) . ' vs ' . self::typeOf($p);
        }
        if (isset($s['items'], $p['items'])) {
            $difference = $this->compare($service, $s['items'], $p['items']);
            if ($difference !== null) {
                return 'items: ' . $difference;
            }
        }
        if (is_array($s['additionalProperties'] ?? null) && is_array($p['additionalProperties'] ?? null)) {
            $difference = $this->compare($service, $s['additionalProperties'], $p['additionalProperties']);
            if ($difference !== null) {
                return 'additionalProperties: ' . $difference;
            }
        }
        $portalProperties = self::properties($p);
        $serviceProperties = self::properties($s);
        foreach (array_keys($serviceProperties) as $name) {
            if (array_key_exists($name, $portalProperties)) {
                $difference = $this->compare($service, $serviceProperties[$name], $portalProperties[$name]);
                if ($difference !== null) {
                    return $name . ': ' . $difference;
                }
            }
        }

        return null;
    }

    private function pair(string $service, string $serviceName, string $portalName): void
    {
        $out = $service . '.' . $serviceName;
        if (isset($this->pairs[$out][$portalName])) {
            return;
        }
        if (!array_key_exists($serviceName, $this->serviceDefinitions[$service])) {
            throw new SpecException(sprintf('%s references the missing definition %s', $service, $serviceName));
        }
        if (!array_key_exists($portalName, $this->portalDefinitions)) {
            throw new SpecException(sprintf('The portal references the missing definition %s', $portalName));
        }
        $this->pairs[$out][$portalName] = true;
        $this->portalToService[$portalName][$service][$serviceName] = true;

        $serviceDefinition = Json::object($this->serviceDefinitions[$service][$serviceName], $out);
        $portalDefinition = Json::object($this->portalDefinitions[$portalName], "portal $portalName");
        $portalProperties = self::properties($portalDefinition);
        $serviceProperties = self::properties($serviceDefinition);
        foreach (array_keys($serviceProperties) as $name) {
            if (array_key_exists($name, $portalProperties)) {
                $difference = $this->compare($service, $serviceProperties[$name], $portalProperties[$name]);
                if ($difference !== null) {
                    $this->schemaConflict("definitions/$out/properties/$name", $difference);
                }
            }
        }
        foreach (['items', 'additionalProperties'] as $nested) {
            if (is_array($serviceDefinition[$nested] ?? null) && is_array($portalDefinition[$nested] ?? null)) {
                $difference = $this->compare($service, $serviceDefinition[$nested], $portalDefinition[$nested]);
                if ($difference !== null) {
                    $this->schemaConflict("definitions/$out/$nested", $difference);
                }
            }
        }
    }

    /**
     * @param array<array-key, mixed> $serviceOperation
     * @param array<array-key, mixed> $portalOperation
     *
     * @return array<array-key, mixed>
     */
    private function sharedOperation(string $key, string $service, array $serviceOperation, array $portalOperation): array
    {
        $operation = Json::object($this->rewrite($serviceOperation, static fn (string $name): string => "$service.$name"), $key);
        $operation['operationId'] = Json::value($portalOperation['operationId'] ?? $serviceOperation['operationId'] ?? null, "$key operationId");
        if (isset($portalOperation['tags'])) {
            $operation['tags'] = Json::value($portalOperation['tags'], "$key tags");
        }

        $parameters = array_values(self::parametersById($operation, $key));
        $positions = array_flip(array_keys(self::parametersById($operation, $key)));
        foreach (self::parametersById($portalOperation, $key) as $id => $portalParameter) {
            $location = "paths/$key/parameters/$id";
            if (!isset($positions[$id])) {
                $parameters[] = $this->fromPortal($portalParameter, $service);
                $this->additions[$location] = true;
            } elseif ($this->portalWins($location)) {
                $parameters[$positions[$id]] = $this->fromPortal($portalParameter, $service);
            }
        }
        if ($parameters !== []) {
            $operation['parameters'] = $parameters;
        }

        $responses = Json::object($operation['responses'] ?? [], "$key responses");
        foreach (Json::objectMap($portalOperation['responses'] ?? [], "$key responses") as $code => $portalResponse) {
            $location = "paths/$key/responses/$code";
            if (!array_key_exists($code, $responses)) {
                $responses[$code] = $this->fromPortal($portalResponse, $service);
                $this->additions[$location] = true;
            } elseif ($this->portalWins($location)) {
                $responses[$code] = $this->fromPortal($portalResponse, $service);
            }
        }
        if ($responses !== []) {
            $operation['responses'] = $responses;
        }

        if ($this->portalWins("paths/$key/security")) {
            $operation['security'] = Json::value($portalOperation['security'] ?? null, "$key security");
        }
        $operation['x-openprovider-operation'] = [
            'service' => $service,
            'operationId' => $serviceOperation['operationId'] ?? null,
        ];

        return $operation;
    }

    /**
     * @param array<array-key, mixed> $portalOperation
     *
     * @return array<array-key, mixed>
     */
    private function portalOperation(array $portalOperation): array
    {
        $operation = Json::object($this->rewrite($portalOperation, self::portalName(...)), 'portal operation');
        $operation['x-experimental'] = true;
        $operation['x-openprovider-operation'] = ['source' => SpecMerger::PORTAL_SOURCE];

        return $operation;
    }

    /**
     * Applies the operationId renames and reports every operationId used more than once.
     *
     * @param array<string, array<array-key, mixed>> $operations
     *
     * @return array<string, Conflict>
     */
    private function applyOperationIds(array &$operations): array
    {
        $original = [];
        foreach ($operations as $key => $operation) {
            $original[$key] = Json::string($operation['operationId'] ?? null, "$key operationId");
        }
        foreach ($this->overrides->operationIds as $key => $rename) {
            if (!isset($operations[$key])) {
                throw new SpecException(sprintf('overrides.operationIds names %s, which is not one of the merged operations', $key));
            }
            $operations[$key]['operationId'] = $rename->use;
        }
        $final = [];
        foreach ($operations as $key => $operation) {
            $final[$key] = Json::string($operation['operationId'], "$key operationId");
        }

        $conflicts = [];
        $finalGroups = self::groupKeys($final);
        foreach (self::groupKeys($original) as $operationId => $keys) {
            if (count($keys) < 2) {
                continue;
            }
            $stillShared = false;
            foreach ($keys as $key) {
                $stillShared = $stillShared || count($finalGroups[$final[$key]]) > 1;
            }
            $renames = array_intersect_key($this->overrides->operationIds, array_flip($keys));
            $resolution = $stillShared ? null : new Resolution(
                implode('; ', array_map(static fn (string $k, Resolution $r): string => "$k → {$r->use}", array_keys($renames), $renames)),
                implode(' ', array_unique(array_map(static fn (Resolution $r): string => $r->reason, $renames))),
            );
            $id = "operationId/$operationId";
            $conflicts[$id] = new Conflict($id, 'used by ' . implode(', ', $keys), $resolution);
        }
        foreach ($finalGroups as $operationId => $keys) {
            $id = "operationId/$operationId";
            if (count($keys) > 1 && !isset($conflicts[$id])) {
                $conflicts[$id] = new Conflict($id, 'used by ' . implode(', ', $keys) . ' after the renames in overrides.json');
            }
        }

        return $conflicts;
    }

    /**
     * @param array<string, array<array-key, mixed>> $operations
     *
     * @return array<string, array<array-key, mixed>>
     */
    private function reachableDefinitions(array $operations): array
    {
        $queue = self::references($operations);
        $definitions = [];
        while ($queue !== []) {
            $name = array_shift($queue);
            if (isset($definitions[$name])) {
                continue;
            }
            $definitions[$name] = $this->buildDefinition($name);
            foreach (self::references($definitions[$name]) as $reference) {
                if (!isset($definitions[$reference])) {
                    $queue[] = $reference;
                }
            }
        }
        ksort($definitions, SORT_STRING);

        return $definitions;
    }

    /**
     * @return array<array-key, mixed>
     */
    private function buildDefinition(string $out): array
    {
        [$namespace, $name] = explode('.', $out, 2) + [1 => ''];
        if ($namespace === SpecMerger::PORTAL) {
            if (!array_key_exists($name, $this->portalDefinitions)) {
                throw new SpecException(sprintf('The portal references the missing definition %s', $name));
            }
            $definition = Json::object($this->rewrite($this->portalDefinitions[$name], self::portalName(...)), $out);
            $definition['x-experimental'] = true;
            $definition[self::SOURCE_MARK] = SpecMerger::PORTAL_SOURCE;

            return $definition;
        }
        if (!isset($this->serviceDefinitions[$namespace]) || !array_key_exists($name, $this->serviceDefinitions[$namespace])) {
            throw new SpecException(sprintf('The merged spec references the missing definition %s', $out));
        }

        $definition = Json::object(
            $this->rewrite($this->serviceDefinitions[$namespace][$name], static fn (string $n): string => "$namespace.$n"),
            $out,
        );
        $portalNames = array_keys($this->pairs[$out] ?? []);
        sort($portalNames, SORT_STRING);
        foreach ($portalNames as $portalName) {
            $properties = self::properties($definition);
            $before = $properties;
            $portalProperties = self::properties(Json::object($this->portalDefinitions[$portalName], $portalName));
            foreach (array_keys($portalProperties) as $property) {
                $location = "definitions/$out/properties/$property";
                if (!array_key_exists($property, $properties)) {
                    $properties[$property] = $this->fromPortal($portalProperties[$property], $namespace);
                    $this->additions[$location] = true;
                } elseif ($this->portalWins($location)) {
                    $properties[$property] = $this->fromPortal($portalProperties[$property], $namespace);
                } else {
                    $properties[$property] = $this->uniteEnums($properties[$property], $portalProperties[$property], $location);
                }
            }
            if ($properties !== $before) {
                $definition['properties'] = $properties;
            }
        }

        return $definition;
    }

    /**
     * @return array<array-key, mixed>|\stdClass|scalar|null
     */
    private function uniteEnums(mixed $serviceSchema, mixed $portalSchema, string $location): array|\stdClass|bool|int|float|string|null
    {
        if (!is_array($serviceSchema) || !is_array($portalSchema)) {
            return Json::value($serviceSchema, $location);
        }
        if (isset($serviceSchema['enum'], $portalSchema['enum']) && is_array($serviceSchema['enum']) && is_array($portalSchema['enum'])) {
            $known = array_values($serviceSchema['enum']);
            $missing = array_values(array_filter(
                $portalSchema['enum'],
                static fn (mixed $value): bool => !in_array($value, $known, true),
            ));
            if ($missing !== []) {
                $serviceSchema['enum'] = array_merge($known, $missing);
                $this->enumUnions[$location] = true;
            }
        }
        if (isset($serviceSchema['items'], $portalSchema['items'])) {
            $serviceSchema['items'] = $this->uniteEnums($serviceSchema['items'], $portalSchema['items'], $location . '/items');
        }

        return $serviceSchema;
    }

    private function portalWins(string $location): bool
    {
        return isset($this->schemaConflicts[$location])
            && ($this->overrides->conflicts[$location] ?? null)?->use === Resolution::PORTAL;
    }

    /**
     * A portal schema placed into a service's output: its references point at the service's
     * definition when the pairing proved which one it is, otherwise at a `portal.` import.
     */
    /**
     * @return array<array-key, mixed>|\stdClass|scalar|null
     */
    private function fromPortal(mixed $schema, string $service): array|\stdClass|bool|int|float|string|null
    {
        $rewritten = $this->rewrite($schema, function (string $name) use ($service): string {
            $candidates = $this->portalToService[$name][$service] ?? [];

            return count($candidates) === 1 ? $service . '.' . array_key_first($candidates) : self::portalName($name);
        });
        if (is_array($rewritten)) {
            $rewritten[self::SOURCE_MARK] = SpecMerger::PORTAL_SOURCE;
        }

        return $rewritten;
    }

    private static function portalName(string $name): string
    {
        return SpecMerger::PORTAL . '.' . $name;
    }

    private function rejectStaleOverrides(): void
    {
        $stale = array_diff(array_keys($this->overrides->conflicts), array_keys($this->schemaConflicts));
        if ($stale !== []) {
            throw new SpecException('overrides.conflicts settles conflicts the merge no longer has: ' . implode(', ', $stale));
        }
    }

    private function schemaConflict(string $id, string $detail): void
    {
        $this->schemaConflicts[$id] ??= $detail;
    }

    /**
     * Every `$ref` at any depth renamed through $rename.
     *
     * @param \Closure(string): string $rename
     *
     * @return array<array-key, mixed>|\stdClass|scalar|null
     */
    private function rewrite(mixed $value, \Closure $rename): array|\stdClass|bool|int|float|string|null
    {
        if (!is_array($value)) {
            return Json::value($value, 'schema');
        }

        return Json::mapValues($value, fn (mixed $item, int|string $key): mixed => $key === '$ref'
            ? self::DEFINITIONS . $rename(self::definitionName($item))
            : $this->rewrite($item, $rename));
    }

    /**
     * @return list<string>
     */
    private static function references(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $found = [];
        foreach (array_keys($value) as $key) {
            if ($key === '$ref') {
                $found[] = self::definitionName($value[$key]);
            } else {
                array_push($found, ...self::references($value[$key]));
            }
        }

        return $found;
    }

    private static function definitionName(mixed $reference): string
    {
        $name = is_string($reference) && str_starts_with($reference, self::DEFINITIONS) ? substr($reference, strlen(self::DEFINITIONS)) : null;
        if ($name === null) {
            throw new SpecException(sprintf('Only local definition references are supported, got %s', self::show($reference)));
        }

        return $name;
    }

    /**
     * @param array<array-key, mixed> $schema
     */
    private static function refName(array $schema): ?string
    {
        return array_key_exists('$ref', $schema) ? self::definitionName($schema['$ref']) : null;
    }

    /**
     * @param array<array-key, mixed> $schema
     */
    private static function typeOf(array $schema): string
    {
        $type = Json::optionalString($schema, 'type')
            ?? (isset($schema['properties']) || isset($schema['additionalProperties']) ? 'object' : 'any');
        $format = Json::optionalString($schema, 'format');

        return $format !== null ? "$type:$format" : $type;
    }

    /**
     * @param array<array-key, mixed> $schema
     */
    private static function describe(array $schema): string
    {
        $reference = self::refName($schema);

        return $reference !== null ? '$ref ' . $reference : self::typeOf($schema);
    }

    /**
     * @param array<array-key, mixed> $schema
     *
     * @return array<array-key, mixed>
     */
    private static function properties(array $schema): array
    {
        return Json::object($schema['properties'] ?? [], 'properties');
    }

    /**
     * @param array<array-key, mixed> $operation
     *
     * @return array<string, array<array-key, mixed>> "in:name" => parameter, in declaration order
     */
    private static function parametersById(array $operation, string $key): array
    {
        $parameters = [];
        foreach (Json::objectList($operation['parameters'] ?? [], "$key parameters") as $parameter) {
            $id = Json::string($parameter['in'] ?? null, "$key parameter in") . ':'
                . Json::string($parameter['name'] ?? null, "$key parameter name");
            $parameters[$id] = $parameter;
        }

        return $parameters;
    }

    /**
     * Descriptions and an explicit `required: false` (the default) are not differences.
     *
     * @param array<array-key, mixed> $service
     * @param array<array-key, mixed> $portal
     */
    private static function compareParameters(array $service, array $portal): ?string
    {
        $normalize = static function (array $parameter): array {
            unset($parameter['description']);
            if (($parameter['required'] ?? null) === false) {
                unset($parameter['required']);
            }

            return array_filter($parameter, static fn (int|string $k): bool => !str_starts_with((string) $k, 'x-'), ARRAY_FILTER_USE_KEY);
        };
        $s = $normalize($service);
        $p = $normalize($portal);
        if ($s == $p) {
            return null;
        }
        ksort($s);
        ksort($p);

        return sprintf('service %s vs portal %s', self::show($s), self::show($p));
    }

    private static function show(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string, string> $operationIds key => operationId
     *
     * @return array<string, list<string>> operationId => keys
     */
    private static function groupKeys(array $operationIds): array
    {
        $groups = [];
        foreach ($operationIds as $key => $operationId) {
            $groups[$operationId][] = $key;
        }

        return $groups;
    }

    /**
     * @param array<array-key, mixed>                $portal
     * @param array<array-key, mixed>                $sources
     * @param array<string, array<array-key, mixed>> $operations
     * @param array<string, array<array-key, mixed>> $definitions
     *
     * @return array<string, mixed>
     */
    private function assemble(array $portal, array $sources, array $operations, array $definitions): array
    {
        $info = Json::object($portal['info'] ?? [], 'portal info');
        $spec = [
            'swagger' => '2.0',
            'info' => [
                'title' => 'Openprovider REST API',
                'description' => 'Generated by tools/spec-merge from the sources listed in spec/PROVENANCE.md. '
                    . 'Do not edit: change spec/overrides.json and run the merge again.',
                'version' => $info['version'] ?? 'unknown',
            ],
        ];
        foreach (['host'] as $key) {
            if (isset($portal[$key])) {
                $spec[$key] = Json::value($portal[$key], "portal $key");
            }
        }
        $spec['basePath'] = SpecMerger::BASE_PATH;
        foreach (['schemes', 'consumes', 'produces', 'securityDefinitions', 'security', 'tags', 'x-tagGroups'] as $key) {
            if (isset($portal[$key])) {
                $spec[$key] = Json::value($portal[$key], "portal $key");
            }
        }
        if ($sources !== []) {
            $spec['x-openprovider-sources'] = $sources;
        }

        $paths = [];
        foreach ($operations as $key => $operation) {
            [$method, $path] = explode(' ', $key, 2) + [1 => ''];
            $paths[$path][strtolower($method)] = $operation;
        }
        ksort($paths, SORT_STRING);
        foreach ($paths as $path => $item) {
            $ordered = [];
            foreach (self::METHODS as $method) {
                if (isset($item[$method])) {
                    $ordered[$method] = $item[$method];
                }
            }
            $paths[$path] = $ordered;
        }
        $spec['paths'] = $paths;
        $spec['definitions'] = $definitions;

        return $spec;
    }
}
