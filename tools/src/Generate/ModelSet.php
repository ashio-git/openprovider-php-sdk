<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Generate;

use Ashio\OpenProvider\Tools\Spec\Json;
use Ashio\OpenProvider\Tools\Spec\SpecException;

/**
 * Which definitions become generated classes.
 *
 * The roots are what callers exchange with the API: request bodies, and the `data` of success
 * envelopes. Everything those reach through `$ref` is generated. The envelopes themselves
 * (`{code, desc, data, warnings, maintenance}`) are read by Response\ApiResponse, the `default`
 * error schemas by the exception factory, and their warnings by Response\Warning, so they are
 * not models unless a root reaches them too.
 */
final class ModelSet
{
    private const METHODS = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch'];
    private const ENVELOPE_FIELDS = ['code', 'desc', 'data', 'warnings', 'maintenance'];

    /**
     * Every list is sorted by definition name.
     *
     * @param list<string> $models      reachable objects
     * @param list<string> $enums       reachable string enums
     * @param list<string> $unsupported reachable, but neither an object nor a string enum
     * @param list<string> $envelopes   success envelopes, read by ApiResponse
     * @param list<string> $errors      error schemas, read by the exception factory
     * @param list<string> $warnings    reached only from envelope or error warnings, read by Warning
     * @param list<string> $unreachable none of the above
     */
    private function __construct(
        public readonly array $models,
        public readonly array $enums,
        public readonly array $unsupported,
        public readonly array $envelopes,
        public readonly array $errors,
        public readonly array $warnings,
        public readonly array $unreachable,
    ) {
    }

    /**
     * @param array<array-key, mixed> $spec the decoded spec
     */
    public static function fromSpec(array $spec): self
    {
        $definitions = Json::object($spec['definitions'] ?? null, 'definitions');
        $roots = [];
        $envelopes = [];
        $errors = [];
        $warnings = [];
        foreach (Json::objectMap($spec['paths'] ?? null, 'paths') as $path => $item) {
            foreach (array_keys($item) as $method) {
                if (!in_array($method, self::METHODS, true)) {
                    continue;
                }
                $operation = Json::object($item[$method], "$method $path");
                $id = isset($operation['operationId']) && is_string($operation['operationId'])
                    ? $operation['operationId']
                    : strtoupper($method) . ' ' . $path;
                foreach (Json::objectList($operation['parameters'] ?? [], "$id parameters") as $parameter) {
                    foreach (self::refs($parameter['schema'] ?? null) as $ref) {
                        $roots[$ref] = true;
                    }
                }
                foreach (Json::objectMap($operation['responses'] ?? null, "$id responses") as $status => $response) {
                    if (!isset($response['schema'])) {
                        continue;
                    }
                    $schema = Json::object($response['schema'], "$id $status schema");
                    if (!isset($schema['$ref']) || !is_string($schema['$ref'])) {
                        throw new SpecException(sprintf('%s: the %s response is not a $ref to an envelope', $id, $status));
                    }
                    $name = self::refName($schema['$ref']);
                    $envelope = self::envelopeFields(Json::object($definitions[$name] ?? null, $name), $name);
                    foreach (self::refs($envelope['warnings'] ?? null) as $warning) {
                        $warnings[$warning] = true;
                    }
                    $success = is_int($status) && $status >= 200 && $status < 300;
                    if ($success) {
                        $envelopes[$name] = true;
                        foreach (self::refs($envelope['data'] ?? null) as $data) {
                            $roots[$data] = true;
                        }
                    } else {
                        $errors[$name] = true;
                    }
                    foreach (['code', 'desc', 'maintenance', ...($success ? [] : ['data'])] as $field) {
                        if (self::refs($envelope[$field] ?? null) !== []) {
                            throw new SpecException(sprintf('%s: "%s" refers to a definition, but the core reads it as a scalar', $name, $field));
                        }
                    }
                }
            }
        }

        $reachable = [];
        $queue = array_keys($roots);
        while ($queue !== []) {
            $name = array_pop($queue);
            if (isset($reachable[$name])) {
                continue;
            }
            if (!isset($definitions[$name])) {
                throw new SpecException(sprintf('$ref to %s, which is not a definition', $name));
            }
            $reachable[$name] = true;
            array_push($queue, ...self::refs($definitions[$name]));
        }

        $models = [];
        $enums = [];
        $unsupported = [];
        foreach (array_keys($reachable) as $name) {
            $definition = Json::object($definitions[$name], $name);
            if (($definition['type'] ?? null) === 'string' && isset($definition['enum'])) {
                $enums[] = $name;
            } elseif (($definition['type'] ?? null) === 'object') {
                $models[] = $name;
            } else {
                $unsupported[] = $name;
            }
        }
        $envelopes = array_diff_key($envelopes, $reachable);
        $errors = array_diff_key($errors, $reachable);
        $warnings = array_diff_key($warnings, $reachable, $envelopes, $errors);
        $unreachable = array_diff_key($definitions, $reachable, $envelopes, $errors, $warnings);

        return new self(
            self::sorted($models),
            self::sorted($enums),
            self::sorted($unsupported),
            self::sorted(array_keys($envelopes)),
            self::sorted(array_keys($errors)),
            self::sorted(array_keys($warnings)),
            self::sorted(array_keys($unreachable)),
        );
    }

    /**
     * The definitions a schema refers to through the schema keywords, at any depth (examples and
     * descriptions are data, not schema, and are not searched).
     *
     * @return list<string>
     */
    public static function refs(mixed $schema): array
    {
        if ($schema === null) {
            return [];
        }
        $schema = Json::object($schema, 'schema');
        $refs = isset($schema['$ref']) ? [self::refName($schema['$ref'])] : [];
        foreach (Json::objectMap($schema['properties'] ?? [], 'properties') as $property) {
            array_push($refs, ...self::refs($property));
        }
        foreach (['items', 'additionalProperties', 'not'] as $keyword) {
            if (isset($schema[$keyword]) && !is_bool($schema[$keyword])) {
                array_push($refs, ...self::refs($schema[$keyword]));
            }
        }
        foreach (['allOf', 'anyOf', 'oneOf'] as $keyword) {
            foreach (Json::objectList($schema[$keyword] ?? [], $keyword) as $member) {
                array_push($refs, ...self::refs($member));
            }
        }

        return $refs;
    }

    public static function refName(mixed $ref): string
    {
        $name = is_string($ref) && str_starts_with($ref, '#/definitions/') ? substr($ref, strlen('#/definitions/')) : '';
        if ($name === '') {
            throw new SpecException(sprintf('Unsupported $ref %s: only #/definitions/<name> is', (string) json_encode($ref)));
        }

        return $name;
    }

    /**
     * @param array<array-key, mixed> $definition
     *
     * @return array<array-key, mixed> the envelope's properties, once checked to be the ones the core reads
     */
    private static function envelopeFields(array $definition, string $name): array
    {
        $fields = Json::object($definition['properties'] ?? [], "$name properties");
        foreach (array_keys($fields) as $field) {
            if (!in_array($field, self::ENVELOPE_FIELDS, true)) {
                throw new SpecException(sprintf('%s has "%s", a field ApiResponse does not read', $name, $field));
            }
        }

        return $fields;
    }

    /**
     * @param array<array-key, array-key> $names
     *
     * @return list<string>
     */
    private static function sorted(array $names): array
    {
        $names = array_map('strval', array_values($names));
        sort($names, SORT_STRING);

        return $names;
    }
}
