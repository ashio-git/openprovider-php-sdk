<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Conformance;

use Ashio\OpenProvider\Auth\Credentials;
use Ashio\OpenProvider\Client\AbstractService;
use Ashio\OpenProvider\Client\ApiClient;
use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Response\ApiResponse;
use Ashio\OpenProvider\Serialization\Model;
use Ashio\OpenProvider\Spec\Operation;
use Ashio\OpenProvider\Tools\Generate\ModelSet;
use Ashio\OpenProvider\Tools\Spec\Json;
use Psr\Http\Message\RequestInterface;

/**
 * The conformance audit of the study (docs/study/2026-11-09/conformance.md), made a gate (S2).
 *
 * For each operation of the spec it finds the method that carries its #[Operation] and runs it
 * against a recording transport, the way the study's runtime probe ran the 2.x SDK:
 *
 * - once with only the required arguments: the route, the token, the body and how `data` is
 *   read must be what the spec declares;
 * - once per optional argument: the argument must reach exactly one query parameter of the
 *   spec, under the spec's name and encoding (booleans are probed with false, which a filter
 *   that drops falsy values loses);
 * - and statically: every public method of a service is an operation, one service per tag group,
 *   parameter types follow the spec, portal-only operations say @experimental.
 *
 * Nothing here comes from the generator: the audit reads the spec's JSON and the code as built.
 */
final class Auditor
{
    private const HTTP_METHODS = ['get', 'post', 'put', 'delete', 'patch'];
    private const PHP_TYPES = ['string' => ['string'], 'integer' => ['int'], 'number' => ['float', 'int'], 'boolean' => ['bool'], 'array' => ['array']];

    /** @var array<array-key, mixed> */
    private readonly array $definitions;

    private readonly SpecSamples $samples;

    /** @var array<class-string<Model>, string> */
    private readonly array $definitionOf;

    /** @var list<Divergence> */
    private array $divergences = [];

    /**
     * @param array<array-key, mixed>             $spec     the decoded spec/openprovider.json
     * @param list<class-string<AbstractService>> $services
     * @param array<string, class-string<Model>>  $models   definition => model class (Model\ModelMap::MODELS)
     * @param array<string, string>               $deferred tag group => why its operations have no method yet
     * @param list<string>                        $public   "METHOD /path" served without a token although the spec does not say so
     */
    public function __construct(
        private readonly array $spec,
        private readonly array $services,
        private readonly array $models,
        private readonly array $deferred,
        private readonly array $public,
    ) {
        $this->definitions = Json::object($spec['definitions'] ?? null, 'definitions');
        $this->samples = new SpecSamples($this->definitions);
        $this->definitionOf = array_flip($models);
    }

    public function run(): Report
    {
        $this->divergences = [];
        $operations = $this->operations();
        $methods = $this->methods($operations);
        $conform = [];
        $pending = [];
        $audited = 0;
        $groups = [];
        foreach ($operations as $id => $operation) {
            $found = $methods[$id] ?? [];
            if ($found === [] && isset($this->deferred[$operation['group']])) {
                $pending[$id] = $operation['group'] . ': ' . $this->deferred[$operation['group']];

                continue;
            }
            ++$audited;
            if ($found === []) {
                $this->major('registry', 'missing_operation', $id, sprintf('%s %s has no method', $operation['method'], $operation['path']));

                continue;
            }
            if (count($found) > 1) {
                $this->major('registry', 'duplicate_operation', $id, 'implemented by ' . implode(' and ', array_map(static fn (array $m): string => $m[0] . '::' . $m[1] . '()', $found)));
            }
            foreach ($found as [$class, $method, $attribute]) {
                $groups[$class][$operation['group']] = true;
                $this->probe($id, $operation, $class, $method, $attribute);
            }
            $conform[] = $id;
        }
        foreach ($groups as $class => $classGroups) {
            if (count($classGroups) > 1) {
                $this->major('registry', 'mixed_service', $class, 'one service per tag group, but it holds ' . implode(', ', array_keys($classGroups)));
            }
        }
        $byGroup = [];
        foreach ($groups as $class => $classGroups) {
            foreach (array_keys($classGroups) as $group) {
                $byGroup[$group][] = $class;
            }
        }
        foreach ($byGroup as $group => $classes) {
            if (count($classes) > 1) {
                $this->minor('registry', 'split_group', $group, 'the tag group is spread over ' . implode(', ', $classes));
            }
        }
        $conform = array_values(array_filter($conform, fn (string $id): bool => $this->isClean($id)));

        return new Report($this->divergences, $conform, $pending, $audited);
    }

    /**
     * @return array<string, array{method: string, path: string, group: string, operation: array<array-key, mixed>}>
     */
    private function operations(): array
    {
        $groupOf = [];
        foreach (Json::list($this->spec['x-tagGroups'] ?? [], 'x-tagGroups') as $group) {
            $group = Json::object($group, 'x-tagGroups entry');
            foreach (Json::list($group['tags'] ?? [], 'x-tagGroups tags') as $tag) {
                $groupOf[Json::string($tag, 'tag')] = Json::string($group['name'] ?? null, 'x-tagGroups name');
            }
        }
        $operations = [];
        foreach (Json::object($this->spec['paths'] ?? null, 'paths') as $path => $item) {
            foreach (Json::object($item, "paths.$path") as $method => $operation) {
                if (!in_array($method, self::HTTP_METHODS, true)) {
                    continue;
                }
                $operation = Json::object($operation, "$method $path");
                $tag = Json::list($operation['tags'] ?? [], 'tags')[0] ?? null;
                $operations[Json::string($operation['operationId'] ?? null, "$method $path operationId")] = [
                    'method' => strtoupper((string) $method),
                    'path' => (string) $path,
                    'group' => $groupOf[is_string($tag) ? $tag : ''] ?? '',
                    'operation' => $operation,
                ];
            }
        }

        return $operations;
    }

    /**
     * Every #[Operation] method, by operationId; reports public methods that are no operation
     * and operationIds the spec does not have.
     *
     * @param array<string, array{method: string, path: string, group: string, operation: array<array-key, mixed>}> $operations
     *
     * @return array<string, list<array{class-string<AbstractService>, string, Operation}>>
     */
    private function methods(array $operations): array
    {
        $methods = [];
        foreach ($this->services as $class) {
            foreach ((new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() === AbstractService::class) {
                    continue;
                }
                $attributes = $method->getAttributes(Operation::class);
                if ($attributes === []) {
                    $this->major('registry', 'untraced_method', $class . '::' . $method->getName(), 'a public method of a service that implements no operation');

                    continue;
                }
                $attribute = $attributes[0]->newInstance();
                if (!isset($operations[$attribute->operationId])) {
                    $this->major('registry', 'unknown_operation', $class . '::' . $method->getName(), sprintf('#[Operation] names %s, which the spec does not have', $attribute->operationId));

                    continue;
                }
                $methods[$attribute->operationId][] = [$class, $method->getName(), $attribute];
            }
        }

        return $methods;
    }

    /**
     * @param array{method: string, path: string, group: string, operation: array<array-key, mixed>} $operation
     * @param class-string<AbstractService>                                                        $class
     */
    private function probe(string $id, array $operation, string $class, string $method, Operation $attribute): void
    {
        if ([$attribute->method, $attribute->path] !== [$operation['method'], $operation['path']]) {
            $this->major('registry', 'wrong_route', $id, sprintf('#[Operation] says %s %s, the spec %s %s', $attribute->method, $attribute->path, $operation['method'], $operation['path']));
        }
        $reflection = new \ReflectionMethod($class, $method);
        $spec = $operation['operation'];
        $query = [];
        $bodyDefinition = null;
        foreach (Json::list($spec['parameters'] ?? [], "$id parameters") as $parameter) {
            $parameter = Json::object($parameter, "$id parameter");
            $name = Json::string($parameter['name'] ?? null, "$id parameter name");
            if (($parameter['in'] ?? null) === 'query') {
                $query[$name] = $parameter;
            } elseif (($parameter['in'] ?? null) === 'body') {
                $bodyDefinition = ModelSet::refName(Json::object($parameter['schema'] ?? null, "$id body")['$ref'] ?? null);
            }
        }
        if (($spec['x-experimental'] ?? false) === true && !str_contains((string) $reflection->getDocComment(), '@experimental')) {
            $this->minor('docs', 'experimental_undocumented', $id, 'documented only by the developer portal, but the method does not say @experimental');
        }

        $base = [];
        $bodyParameter = null;
        foreach ($reflection->getParameters() as $seed => $parameter) {
            if ($bodyDefinition !== null && $this->isBodyParameter($parameter)) {
                $bodyParameter = $parameter;
            }
            if ($parameter->isOptional()) {
                continue;
            }
            $value = $this->sample($parameter, $seed, $parameter === $bodyParameter ? $bodyDefinition : null);
            if ($value === null) {
                $this->major('params', 'unprobeable', $id, sprintf('$%s has a type the audit cannot fill', $parameter->getName()));

                return;
            }
            $base[$parameter->getName()] = $value;
        }
        $baseline = $this->call($operation, $class, $method, $base);
        if ($baseline['error'] !== null || $baseline['request'] === null) {
            $this->major('params', 'call_failed', $id, 'with its required arguments the method throws ' . ($baseline['error'] === null ? 'nothing but sends nothing' : get_class($baseline['error']) . ': ' . $baseline['error']->getMessage()));

            return;
        }
        $request = $baseline['request'];
        $this->checkRoute($id, $operation, $request, $base);
        $this->checkAuth($id, $operation, $request);
        $this->checkBody($id, $operation, $request, $bodyDefinition, $bodyParameter);
        $this->checkResponse($id, $spec, $baseline['result'], $baseline['data']);

        $baselinePairs = self::pairs($request);
        foreach ($baselinePairs as [$name]) {
            if (!isset($query[$name])) {
                $this->major('params', 'param_not_in_spec', $id, sprintf('sends the query parameter "%s", which the spec does not declare', $name));
            }
        }
        $reached = [];
        foreach ($baselinePairs as [$name]) {
            $reached[$name] = true;
        }
        foreach ($reflection->getParameters() as $seed => $parameter) {
            if (!$parameter->isOptional() || $parameter === $bodyParameter) {
                continue;
            }
            $name = $this->probeOptional($id, $operation, $class, $method, $base, $parameter, $seed, $query, $baselinePairs, (string) $request->getBody());
            if ($name !== null) {
                $reached[$name] = true;
            }
        }
        foreach (array_keys($query) as $name) {
            if (!isset($reached[$name])) {
                $this->minor('params', 'missing_param', $id, sprintf('the spec\'s query parameter "%s" has no argument', $name));
            }
        }
    }

    /**
     * Calls the method with one optional argument set and returns the query parameter it reached.
     *
     * @param array{method: string, path: string, group: string, operation: array<array-key, mixed>} $operation
     * @param class-string<AbstractService>                                                        $class
     * @param array<string, mixed>                                                                 $base
     * @param array<string, array<array-key, mixed>>                                               $query
     * @param list<array{string, string}>                                                          $baselinePairs
     */
    private function probeOptional(string $id, array $operation, string $class, string $method, array $base, \ReflectionParameter $parameter, int $seed, array $query, array $baselinePairs, string $baselineBody): ?string
    {
        $variable = '$' . $parameter->getName();
        $value = $this->sample($parameter, $seed, null);
        if ($value === null) {
            $this->major('params', 'unprobeable', $id, sprintf('%s has a type the audit cannot fill', $variable));

            return null;
        }
        $probe = $this->call($operation, $class, $method, [...$base, $parameter->getName() => $value]);
        if ($probe['error'] !== null || $probe['request'] === null) {
            $this->major('params', 'call_failed', $id, sprintf('setting %s makes the method throw %s', $variable, $probe['error'] === null ? 'nothing but send nothing' : get_class($probe['error']) . ': ' . $probe['error']->getMessage()));

            return null;
        }
        $added = self::difference(self::pairs($probe['request']), $baselinePairs);
        if ($added === []) {
            $body = (string) $probe['request']->getBody();
            $moved = $body !== $baselineBody ? array_values(array_intersect(array_keys($query), self::keys($body))) : [];
            if ($moved !== []) {
                $this->major('params', 'query_sent_as_body', $id, sprintf('%s goes into the JSON body as "%s", but the spec reads it from the query', $variable, $moved[0]));
            } else {
                $this->major('params', 'param_ignored', $id, sprintf('%s = %s never reaches the request', $variable, (string) json_encode($value)));
            }

            return null;
        }
        $names = array_values(array_unique(array_map(static fn (array $pair): string => $pair[0], $added)));
        if (count($names) > 1) {
            $this->major('params', 'param_ambiguous', $id, sprintf('%s reaches several query parameters: %s', $variable, implode(', ', $names)));

            return null;
        }
        $name = $names[0];
        if (!isset($query[$name])) {
            $similar = self::similar($name, array_keys($query));
            $this->major('params', 'wrong_param_name', $id, sprintf('%s is sent as "%s", which the spec does not declare%s', $variable, $name, $similar === null ? '' : " (the spec's name is \"$similar\")"));

            return null;
        }
        $spec = $query[$name];
        $expected = self::encoded($name, $value, $spec);
        if (self::canonicalPairs($added) !== self::canonicalPairs($expected)) {
            $this->major('params', ($spec['type'] ?? null) === 'array' ? 'array_query_format' : 'param_encoding', $id, sprintf('%s is sent as %s, the spec expects %s', $variable, self::query($added), self::query($expected)));
        }
        $this->checkType($id, $variable, $parameter, $spec, $name);

        return $name;
    }

    /**
     * @param array{method: string, path: string, group: string, operation: array<array-key, mixed>} $operation
     * @param array<string, mixed>                                                                 $arguments
     */
    private function checkRoute(string $id, array $operation, RequestInterface $request, array $arguments): void
    {
        if ($request->getMethod() !== $operation['method']) {
            $this->major('registry', 'wrong_method', $id, sprintf('sends %s, the spec says %s', $request->getMethod(), $operation['method']));
        }
        $path = $request->getUri()->getPath();
        $basePath = is_string($this->spec['basePath'] ?? null) ? $this->spec['basePath'] : '';
        $pattern = '#^' . preg_quote($basePath, '#') . preg_replace_callback(
            '/\\\\\{([^}]+)\\\\\}/',
            static fn (array $m): string => '(?<p' . bin2hex($m[1]) . '>[^/]+)',
            preg_quote($operation['path'], '#'),
        ) . '$#';
        if (preg_match($pattern, $path, $matches) !== 1) {
            $this->major('params', 'wrong_path', $id, sprintf('sends %s, the spec\'s path is %s%s', $path, $basePath, $operation['path']));

            return;
        }
        $texts = array_map(static fn (mixed $v): ?string => self::text($v), $arguments);
        foreach ($matches as $group => $segment) {
            if (!is_string($group) || !str_starts_with($group, 'p')) {
                continue;
            }
            $placeholder = (string) hex2bin(substr($group, 1));
            if (!in_array(rawurldecode($segment), $texts, true)) {
                $this->major('params', 'path_param_unmapped', $id, sprintf('{%s} is "%s", which is none of the arguments', $placeholder, rawurldecode($segment)));
            }
        }
    }

    /**
     * @param array{method: string, path: string, group: string, operation: array<array-key, mixed>} $operation
     */
    private function checkAuth(string $id, array $operation, RequestInterface $request): void
    {
        $public = ($operation['operation']['security'] ?? null) === [] || in_array($operation['method'] . ' ' . $operation['path'], $this->public, true);
        $sent = $request->getHeaderLine('Authorization') !== '';
        if ($sent === $public) {
            $this->major('auth', 'auth', $id, $public ? 'sends a token to an operation the API serves without one' : 'sends no token');
        }
    }

    /**
     * @param array{method: string, path: string, group: string, operation: array<array-key, mixed>} $operation
     */
    private function checkBody(string $id, array $operation, RequestInterface $request, ?string $definition, ?\ReflectionParameter $parameter): void
    {
        $body = (string) $request->getBody();
        if ($definition === null) {
            if ($body !== '') {
                $this->major('body', $operation['method'] === 'GET' ? 'body_on_get' : 'body_not_in_spec', $id, sprintf('sends a body (%s), the spec declares none', $body));
            }

            return;
        }
        $class = $this->models[$definition] ?? null;
        $type = $parameter?->getType();
        if ($class === null || $parameter === null || !$type instanceof \ReflectionNamedType || $type->getName() !== $class) {
            $this->major('body', 'body_model', $id, sprintf('the body is %s, the spec\'s body is the model %s (%s)', $parameter === null ? 'not a parameter' : 'typed ' . (string) $type, $class ?? '?', $definition));

            return;
        }
        $expected = self::withoutNulls($this->samples->object($definition));
        $sent = $body === '' ? null : json_decode($body, true);
        if (self::canonical($sent) !== self::canonical($expected)) {
            $this->major('body', 'body_mismatch', $id, sprintf('sends %s for the spec\'s example %s', $body, (string) json_encode($expected)));
        }
        $typed = $this->samples->typed($definition);
        $model = $class::fromArray($typed);
        $extra = (new \ReflectionProperty($model, 'extra'))->getValue($model);
        $untyped = array_map('strval', array_keys(array_intersect_key(is_array($extra) ? $extra : [], $typed)));
        if ($untyped !== []) {
            $this->minor('body', 'missing_field', $id, sprintf('%s has no typed field for %s', $class, implode(', ', $untyped)));
        }
    }

    /**
     * @param array<array-key, mixed> $spec
     */
    private function checkResponse(string $id, array $spec, mixed $result, mixed $data): void
    {
        $envelope = $this->envelope($spec);
        $schema = Json::object(Json::object(Json::object($this->definitions[$envelope] ?? null, $envelope)['properties'] ?? [], "$envelope properties")['data'] ?? [], "$envelope data");
        $list = ($schema['type'] ?? null) === 'array';
        $definition = ModelSet::refName(($list ? Json::object($schema['items'] ?? null, "$envelope items") : $schema)['$ref'] ?? null);
        $class = $this->models[$definition] ?? '?';
        if (!$result instanceof ApiResponse) {
            $this->major('response', 'return_type', $id, sprintf('returns %s, not an ApiResponse', get_debug_type($result)));

            return;
        }
        $value = $result->data;
        if (is_array($value) && !$list) {
            $this->minor('response', 'raw_data', $id, sprintf('hands back the raw data; the spec declares the model %s', $class));

            return;
        }
        $items = $list ? (is_array($value) && array_is_list($value) ? $value : null) : [$value];
        $sent = $list ? (is_array($data) ? array_values($data) : []) : [$data ?? []];
        if ($items === null || count($items) !== count($sent)) {
            $this->major('response', 'model_mismatch', $id, sprintf('reads data into %s, the spec declares a list of %s', get_debug_type($value), $class));

            return;
        }
        foreach ($items as $i => $item) {
            if (!$item instanceof $class || !$item instanceof Model) {
                $this->major('response', 'model_mismatch', $id, sprintf('reads data into %s, the spec declares %s', get_debug_type($item), $class));

                return;
            }
            if (self::canonical($item->toArray()) !== self::canonical($sent[$i])) {
                $this->major('response', 'response_lost_fields', $id, sprintf('%s does not give back what the API sent', $class));

                return;
            }
        }
    }

    /**
     * @param array<array-key, mixed> $spec the query parameter
     */
    private function checkType(string $id, string $variable, \ReflectionParameter $parameter, array $spec, string $name): void
    {
        $specType = is_string($spec['type'] ?? null) ? $spec['type'] : '?';
        $expected = self::PHP_TYPES[$specType] ?? [];
        $type = $parameter->getType();
        $actual = [];
        foreach ($type instanceof \ReflectionUnionType ? $type->getTypes() : [$type] as $member) {
            if ($member instanceof \ReflectionNamedType && $member->getName() !== 'null') {
                $actual[] = $member->getName();
            }
        }
        sort($actual);
        sort($expected);
        if ($actual !== $expected) {
            $this->minor('params', 'param_type', $id, sprintf('%s is typed %s, the spec\'s "%s" is %s', $variable, (string) $type, $name, $specType));
        }
        if (($spec['required'] ?? false) === true && $parameter->isOptional()) {
            $this->minor('params', 'param_optional', $id, sprintf('%s is optional, the spec requires "%s"', $variable, $name));
        }
    }

    /**
     * @param array{method: string, path: string, group: string, operation: array<array-key, mixed>} $operation
     * @param class-string<AbstractService>                                                        $class
     * @param array<string, mixed>                                                                 $arguments
     *
     * @return array{request: RequestInterface|null, result: mixed, error: \Throwable|null, data: mixed}
     */
    private function call(array $operation, string $class, string $method, array $arguments): array
    {
        $data = $this->samples->data($this->envelope($operation['operation']));
        $http = new ProbeHttpClient(['code' => 0, 'desc' => '', 'data' => $data], '/auth/login', $operation['path'] === '/auth/login');
        $service = new $class(ApiClient::create(ClientConfig::sandbox(), new Credentials('probe', 'probe'), null, $http));

        try {
            $result = $service->{$method}(...$arguments);
            $error = null;
        } catch (\Throwable $e) {
            $result = null;
            $error = $e;
        }

        return ['request' => $http->last(), 'result' => $result, 'error' => $error, 'data' => $data];
    }

    /**
     * @param array<array-key, mixed> $spec
     */
    private function envelope(array $spec): string
    {
        $responses = Json::object($spec['responses'] ?? null, 'responses');

        return ModelSet::refName(Json::object(Json::object($responses['200'] ?? null, '200')['schema'] ?? null, '200 schema')['$ref'] ?? null);
    }

    private function isBodyParameter(\ReflectionParameter $parameter): bool
    {
        if ($parameter->getName() === 'body') {
            return true;
        }
        $type = $parameter->getType();

        return $type instanceof \ReflectionNamedType && !$type->isBuiltin() && is_subclass_of($type->getName(), Model::class);
    }

    /**
     * A value of the parameter's native type; booleans are false, the value a filter that drops
     * falsy values loses. Null when the type is none the audit can fill.
     */
    private function sample(\ReflectionParameter $parameter, int $seed, ?string $bodyDefinition): mixed
    {
        $type = $parameter->getType();
        $names = [];
        foreach ($type instanceof \ReflectionUnionType ? $type->getTypes() : [$type] as $member) {
            if ($member instanceof \ReflectionNamedType && $member->getName() !== 'null') {
                $names[] = $member->getName();
            }
        }
        if (in_array('float', $names, true)) {
            return 1.5 + (float) $seed;
        }
        $name = $names[0] ?? null;
        if ($name !== null && class_exists($name) && is_subclass_of($name, Model::class)) {
            $definition = $bodyDefinition ?? ($this->definitionOf[$name] ?? null);

            return $definition === null ? null : $name::fromArray($this->samples->object($definition));
        }

        return match ($name) {
            'int' => 40 + $seed,
            'string' => 'v' . $seed . ' ä/&=',
            'bool' => false,
            'array' => $bodyDefinition !== null ? $this->samples->object($bodyDefinition) : ['a' . $seed, 'b' . $seed],
            default => null,
        };
    }

    private function isClean(string $id): bool
    {
        foreach ($this->divergences as $divergence) {
            if ($divergence->operation === $id) {
                return false;
            }
        }

        return true;
    }

    private function major(string $area, string $kind, string $operation, string $message): void
    {
        $this->divergences[] = new Divergence(Divergence::MAJOR, $area, $kind, $operation, $message);
    }

    private function minor(string $area, string $kind, string $operation, string $message): void
    {
        $this->divergences[] = new Divergence(Divergence::MINOR, $area, $kind, $operation, $message);
    }

    /**
     * @return list<array{string, string}> decoded name/value pairs of the query, in order
     */
    private static function pairs(RequestInterface $request): array
    {
        $query = $request->getUri()->getQuery();
        if ($query === '') {
            return [];
        }
        $pairs = [];
        foreach (explode('&', $query) as $pair) {
            $parts = explode('=', $pair, 2);
            $pairs[] = [rawurldecode($parts[0]), rawurldecode($parts[1] ?? '')];
        }

        return $pairs;
    }

    /**
     * The pairs of $all that $base does not have, counting repeats.
     *
     * @param list<array{string, string}> $all
     * @param list<array{string, string}> $base
     *
     * @return list<array{string, string}>
     */
    private static function difference(array $all, array $base): array
    {
        $left = [];
        foreach ($base as $pair) {
            $left[] = $pair[0] . '=' . $pair[1];
        }
        $added = [];
        foreach ($all as $pair) {
            $at = array_search($pair[0] . '=' . $pair[1], $left, true);
            if ($at === false) {
                $added[] = $pair;
            } else {
                unset($left[$at]);
            }
        }

        return $added;
    }

    /**
     * How the spec says $value goes into the query: a pair per item for `multi`, one joined pair
     * otherwise; booleans as words.
     *
     * @param array<array-key, mixed> $spec
     *
     * @return list<array{string, string}>
     */
    private static function encoded(string $name, mixed $value, array $spec): array
    {
        if (!is_array($value)) {
            return [[$name, (string) self::text($value)]];
        }
        $items = array_map(static fn (mixed $item): string => (string) self::text($item), array_values($value));
        $separators = ['csv' => ',', 'ssv' => ' ', 'tsv' => "\t", 'pipes' => '|'];
        $format = is_string($spec['collectionFormat'] ?? null) ? $spec['collectionFormat'] : 'csv';
        if ($format === 'multi') {
            return array_map(static fn (string $item): array => [$name, $item], $items);
        }

        return [[$name, implode($separators[$format] ?? ',', $items)]];
    }

    private static function text(mixed $value): ?string
    {
        return match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            is_string($value) => $value,
            is_int($value), is_float($value) => (string) $value,
            default => null,
        };
    }

    /**
     * @param list<array{string, string}> $pairs
     *
     * @return list<string>
     */
    private static function canonicalPairs(array $pairs): array
    {
        $flat = array_map(static fn (array $pair): string => $pair[0] . '=' . $pair[1], $pairs);
        sort($flat);

        return $flat;
    }

    /**
     * @param list<array{string, string}> $pairs
     */
    private static function query(array $pairs): string
    {
        return implode('&', array_map(static fn (array $pair): string => $pair[0] . '=' . $pair[1], $pairs));
    }

    /**
     * @return list<string> the top-level keys of a JSON object body
     */
    private static function keys(string $body): array
    {
        $decoded = json_decode($body, true);

        return is_array($decoded) ? array_map('strval', array_keys($decoded)) : [];
    }

    /**
     * @param list<string> $candidates
     */
    private static function similar(string $name, array $candidates): ?string
    {
        $normal = static fn (string $s): string => strtolower((string) preg_replace('/[^A-Za-z0-9]/', '', $s));
        foreach ($candidates as $candidate) {
            if ($normal($candidate) === $normal($name)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function withoutNulls(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        $list = array_is_list($value);
        $out = [];
        foreach ($value as $key => $item) {
            if ($item === null && !$list) {
                continue;
            }
            $out[$key] = self::withoutNulls($item);
        }

        return $out;
    }

    /**
     * Maps sorted by key at every depth: JSON objects compare by content, not key order.
     */
    private static function canonical(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        $value = array_map(self::canonical(...), $value);
        if (!array_is_list($value)) {
            ksort($value, SORT_STRING);
        }

        return $value;
    }
}
