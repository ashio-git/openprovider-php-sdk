<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Service;

use Ashio\OpenProvider\Auth\Credentials;
use Ashio\OpenProvider\Client\ApiClient;
use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Model\ModelMap;
use Ashio\OpenProvider\Response\ApiResponse;
use Ashio\OpenProvider\Serialization\Model;
use Ashio\OpenProvider\Tests\Support\ArrayPath;
use Ashio\OpenProvider\Tests\Support\FakeHttpClient;
use Ashio\OpenProvider\Tests\Support\SpecOperations;
use Ashio\OpenProvider\Tests\Support\SpecPayloads;
use Ashio\OpenProvider\Tools\Conformance\SpecSamples;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * One HTTP contract test per implemented operation, against a fake PSR-18 client fed with the
 * spec's own examples (a typed sample where the spec has none). The expectation is computed from
 * the spec's JSON here, not from the generator or the registry:
 *
 * - the method's parameters are exactly the spec's, named by the documented camelCase rule;
 * - method, path (values percent-encoded), query (dotted names, repeated keys for `multi`,
 *   booleans as words, false kept), JSON body and the bearer token are what the spec declares;
 * - the response's `data` is read into the spec's model (or a list of them): a value of the
 *   declared type lands in its typed property, anything else in `$extra`, and toArray() gives
 *   back exactly what the API sent.
 */
final class OperationContractTest extends TestCase
{
    private const TOKEN = 'tok-1';

    /**
     * @return iterable<string, array{string}>
     */
    public static function operations(): iterable
    {
        foreach (array_keys(SpecOperations::all()) as $id) {
            yield $id => [$id];
        }
    }

    #[DataProvider('operations')]
    public function testTheMethodSendsTheOperationAndReadsItsData(string $operationId): void
    {
        $spec = SpecOperations::all()[$operationId];
        $operation = $spec['operation'];
        $methods = SpecOperations::methods()[$operationId] ?? [];
        self::assertCount(1, $methods, "$operationId has no method");
        [$class, $method] = $methods[0];
        $samples = new SpecSamples(SpecOperations::definitions());

        $arguments = [];
        $path = $spec['path'];
        $query = [];
        $body = null;
        foreach (ArrayPath::entries($operation, 'parameters') as $i => $parameter) {
            $parameter = ArrayPath::entries($parameter);
            $name = ArrayPath::at($parameter, 'name');
            self::assertIsString($name);
            if (ArrayPath::at($parameter, 'in') === 'body') {
                $definition = SpecOperations::refName(ArrayPath::entries($parameter, 'schema'));
                $payload = $samples->object($definition);
                $arguments['body'] = ModelMap::MODELS[$definition]::fromArray($payload);
                $body = self::withoutNulls($payload);

                continue;
            }
            $value = SpecSamples::parameter($parameter, (int) $i);
            $arguments[SpecOperations::camel($name)] = $value;
            if (ArrayPath::at($parameter, 'in') === 'path') {
                $path = str_replace('{' . $name . '}', rawurlencode(self::text($value)), $path);
            } else {
                array_push($query, ...self::pairs($name, $value, ArrayPath::at($parameter, 'collectionFormat') ?? 'csv'));
            }
        }
        $parameters = array_map(static fn (\ReflectionParameter $p): string => $p->getName(), (new \ReflectionMethod($class, $method))->getParameters());
        self::assertEqualsCanonicalizing(array_keys($arguments), $parameters, "$class::$method() parameters");

        $public = $operationId === 'Login' || ArrayPath::at($operation, 'security') === [];
        $envelope = SpecOperations::refName(ArrayPath::entries($operation, 'responses', '200', 'schema'));
        $data = $samples->data($envelope);
        $http = new FakeHttpClient();
        if (!$public) {
            $http->login(self::TOKEN);
        }
        $http->reply(['code' => 0, 'desc' => '', 'data' => $data]);
        $service = new $class(ApiClient::create(ClientConfig::sandbox(), new Credentials('reseller', 'pw'), null, $http));

        $response = $service->{$method}(...$arguments);

        self::assertCount($public ? 1 : 2, $http->requests);
        $request = $http->requests[count($http->requests) - 1];
        self::assertSame($spec['method'], $request->getMethod());
        self::assertSame('/v1' . $path, $request->getUri()->getPath());
        self::assertEqualsCanonicalizing($query, self::parseQuery($request->getUri()->getQuery()), 'query');
        self::assertSame($public ? '' : 'Bearer ' . self::TOKEN, $request->getHeaderLine('Authorization'));
        $sent = (string) $request->getBody();
        if ($body === null) {
            self::assertSame('', $sent, 'no body');
        } else {
            self::assertSame(self::canonical($body), self::canonical(json_decode($sent, true, 512, JSON_THROW_ON_ERROR)), 'body');
        }

        self::assertInstanceOf(ApiResponse::class, $response);
        $schema = ArrayPath::entries(SpecOperations::definitions(), $envelope, 'properties', 'data');
        if (ArrayPath::at($schema, 'type') === 'array') {
            $definition = SpecOperations::refName(ArrayPath::entries($schema, 'items'));
            self::assertIsArray($response->data);
            self::assertTrue(array_is_list($response->data));
            self::assertCount(count(ArrayPath::entries(['d' => $data], 'd')), $response->data);
            foreach (ArrayPath::entries(['d' => $data], 'd') as $i => $item) {
                $this->assertReads($definition, ArrayPath::entries($item), $response->data[$i] ?? null);
            }
        } else {
            $this->assertReads(SpecOperations::refName($schema), ArrayPath::entries(['d' => $data], 'd'), $response->data);
        }
    }

    /**
     * @param array<array-key, mixed> $sent
     */
    private function assertReads(string $definition, array $sent, mixed $model): void
    {
        self::assertInstanceOf(ModelMap::MODELS[$definition], $model);
        self::assertInstanceOf(Model::class, $model);
        self::assertSame(self::canonical($sent), self::canonical($model->toArray()), "$definition round trip");

        $definitions = SpecPayloads::definitions(SpecOperations::SPEC);
        $properties = SpecPayloads::properties($definitions, $definition);
        $expectedExtra = array_keys(array_filter(
            $sent,
            static fn (mixed $value, int|string $key): bool => !isset($properties[(string) $key])
                || $value === null
                || !SpecPayloads::fits($definitions, $properties[(string) $key], $value),
            ARRAY_FILTER_USE_BOTH,
        ));
        $extra = ArrayPath::entries(['e' => (new \ReflectionProperty($model, 'extra'))->getValue($model)], 'e');
        self::assertEqualsCanonicalizing($expectedExtra, array_keys($extra), "$definition: only undeclared, null or mistyped values land in \$extra");
    }

    /**
     * @return list<array{string, string}>
     */
    private static function pairs(string $name, mixed $value, mixed $format): array
    {
        if (!is_array($value)) {
            return [[$name, self::text($value)]];
        }
        $items = array_map(self::text(...), array_values($value));
        if ($format === 'multi') {
            return array_map(static fn (string $item): array => [$name, $item], $items);
        }

        return [[$name, implode(',', $items)]];
    }

    /**
     * @return list<array{string, string}>
     */
    private static function parseQuery(string $query): array
    {
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

    private static function text(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            is_int($value), is_float($value), is_string($value) => (string) $value,
            default => throw new \UnexpectedValueException('No text for ' . get_debug_type($value)),
        };
    }

    /**
     * What the encoder sends of a value: null entries of maps are left out, at any depth.
     */
    private static function withoutNulls(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        $out = [];
        foreach ($value as $key => $item) {
            if ($item === null && !array_is_list($value)) {
                continue;
            }
            $out[$key] = self::withoutNulls($item);
        }

        return $out;
    }

    /**
     * Maps sorted by key at every depth, so two JSON objects compare by content, not key order.
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
