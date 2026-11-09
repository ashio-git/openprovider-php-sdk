<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Http;

use Ashio\OpenProvider\Exception\InvalidArgumentException;
use Ashio\OpenProvider\Http\RequestEncoder;
use Ashio\OpenProvider\Spec\OperationSpec;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequestEncoderTest extends TestCase
{
    public function testQueryParametersFollowTheSpecNotTheHttpMethod(): void
    {
        $delete = self::operation('delete', '/mailcow/domains', [
            ['name' => 'domain.name', 'in' => 'query', 'type' => 'string'],
            ['name' => 'domain.extension', 'in' => 'query', 'type' => 'string'],
        ]);

        $encoded = (new RequestEncoder())->encode($delete, ['domain.name' => 'example', 'domain.extension' => 'com']);

        self::assertSame('DELETE', $encoded->method);
        self::assertSame('/mailcow/domains?domain.name=example&domain.extension=com', $encoded->target);
        self::assertNull($encoded->body);
    }

    public function testArraysRepeatTheKeyAndBooleansAreWords(): void
    {
        $list = self::operation('get', '/mailcow/orders', [
            ['name' => 'status', 'in' => 'query', 'type' => 'array', 'items' => ['type' => 'string'], 'collectionFormat' => 'multi'],
            ['name' => 'with_history', 'in' => 'query', 'type' => 'boolean', 'format' => 'boolean'],
            ['name' => 'is_active', 'in' => 'query', 'type' => 'boolean'],
            ['name' => 'limit', 'in' => 'query', 'type' => 'integer', 'format' => 'int32'],
        ]);

        $encoded = (new RequestEncoder())->encode($list, ['status' => ['ACT', 'REQ'], 'with_history' => true, 'is_active' => false, 'limit' => 0]);

        self::assertSame('/mailcow/orders?status=ACT&status=REQ&with_history=true&is_active=false&limit=0', $encoded->target);
    }

    public function testOnlyNullIsDropped(): void
    {
        $list = self::operation('get', '/x', [
            ['name' => 'a', 'in' => 'query', 'type' => 'string'],
            ['name' => 'b', 'in' => 'query', 'type' => 'string'],
            ['name' => 'c', 'in' => 'query', 'type' => 'integer'],
        ]);

        self::assertSame('/x?b=&c=0', (new RequestEncoder())->encode($list, ['a' => null, 'b' => '', 'c' => 0])->target);
    }

    public function testCsvArraysJoinWithCommas(): void
    {
        $list = self::operation('get', '/x', [['name' => 'ids', 'in' => 'query', 'type' => 'array', 'items' => ['type' => 'integer']]]);

        self::assertSame('/x?ids=1%2C2%2C3', (new RequestEncoder())->encode($list, ['ids' => [1, 2, 3]])->target);
    }

    public function testPathParametersAreEncodedAsOneSegment(): void
    {
        $get = self::operation('get', '/dns/zones/{name}/records', [['name' => 'name', 'in' => 'path', 'required' => true, 'type' => 'string']]);

        self::assertSame('/dns/zones/a%2Fb%3Fc%23d%20e/records', (new RequestEncoder())->encode($get, ['name' => 'a/b?c#d e'])->target);
    }

    public function testTheBodyKeepsZeroFalseAndEmptyStringAndDropsNull(): void
    {
        $create = self::operation('post', '/domains', [['name' => 'body', 'in' => 'body', 'required' => true, 'schema' => ['$ref' => '#/definitions/x']]]);

        $encoded = (new RequestEncoder())->encode($create, ['body' => [
            'period' => 0,
            'autorenew' => false,
            'comments' => '',
            'unused' => null,
            'name_servers' => [],
            'domain' => ['name' => 'example', 'extension' => null],
            'ratio' => 1.0,
        ]]);

        self::assertSame(
            '{"period":0,"autorenew":false,"comments":"","name_servers":[],"domain":{"name":"example"},"ratio":1.0}',
            $encoded->body,
        );
        self::assertSame('/domains', $encoded->target);
    }

    public function testAnEmptyBodyIsAnEmptyObject(): void
    {
        $create = self::operation('post', '/x', [['name' => 'body', 'in' => 'body', 'schema' => []]]);

        self::assertSame('{}', (new RequestEncoder())->encode($create, ['body' => []])->body);
        self::assertSame('{}', (new RequestEncoder())->encode($create, [])->body);
    }

    public function testJsonSerializableBodiesAreAccepted(): void
    {
        $create = self::operation('post', '/x', [['name' => 'body', 'in' => 'body', 'schema' => []]]);
        $model = new class () implements \JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return ['a' => 1, 'b' => null];
            }
        };

        self::assertSame('{"a":1}', (new RequestEncoder())->encode($create, ['body' => $model])->body);
    }

    /**
     * @return iterable<string, array{OperationSpec, array<string, mixed>, string}>
     */
    public static function rejectedArguments(): iterable
    {
        $query = self::operation('get', '/customers', [
            ['name' => 'last_name_pattern', 'in' => 'query', 'type' => 'string'],
            ['name' => 'limit', 'in' => 'query', 'type' => 'integer'],
            ['name' => 'with_additional_data', 'in' => 'query', 'type' => 'boolean'],
            ['name' => 'status', 'in' => 'query', 'type' => 'array', 'items' => ['type' => 'string'], 'collectionFormat' => 'multi'],
        ]);
        $path = self::operation('get', '/domains/{id}', [['name' => 'id', 'in' => 'path', 'required' => true, 'type' => 'integer']]);
        $body = self::operation('post', '/x', [['name' => 'body', 'in' => 'body', 'required' => true, 'schema' => []]]);

        yield 'camelCase name the API ignores' => [$query, ['lastNamePattern' => 'x'], 'lastNamePattern'];
        yield 'string for an integer' => [$query, ['limit' => '10'], 'limit'];
        yield 'int for a boolean' => [$query, ['with_additional_data' => 1], 'with_additional_data'];
        yield 'bool for a string' => [$query, ['last_name_pattern' => true], 'last_name_pattern'];
        yield 'map for an array' => [$query, ['status' => ['a' => 'ACT']], 'status'];
        yield 'nested array item' => [$query, ['status' => [['ACT']]], 'status'];
        yield 'missing path parameter' => [$path, [], 'id'];
        yield 'empty path parameter' => [self::operation('get', '/zones/{name}', [['name' => 'name', 'in' => 'path', 'required' => true, 'type' => 'string']]), ['name' => ''], 'name'];
        yield 'missing required body' => [$body, [], 'body'];
        yield 'scalar body' => [$body, ['body' => 'x'], 'body'];
        yield 'invalid utf-8 in body' => [$body, ['body' => ['a' => "\xB1\x31"]], 'body'];
        yield 'infinite float in body' => [$body, ['body' => ['a' => INF]], 'body'];
    }

    /**
     * @param array<string, mixed> $arguments
     */
    #[DataProvider('rejectedArguments')]
    public function testRejectsArgumentsTheSpecDoesNotAllow(OperationSpec $operation, array $arguments, string $mentioned): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($mentioned);

        (new RequestEncoder())->encode($operation, $arguments);
    }

    /**
     * @param list<array<string, mixed>> $parameters
     */
    private static function operation(string $method, string $path, array $parameters): OperationSpec
    {
        return OperationSpec::fromSwagger($method, $path, ['operationId' => 'Op', 'parameters' => $parameters]);
    }
}
