<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Spec;

use Ashio\OpenProvider\Exception\InvalidArgumentException;
use Ashio\OpenProvider\Spec\OperationSpec;
use Ashio\OpenProvider\Spec\ParameterLocation;
use Ashio\OpenProvider\Tools\Spec\Json;
use PHPUnit\Framework\TestCase;

final class OperationSpecTest extends TestCase
{
    public function testBuildsFromASwaggerOperation(): void
    {
        $operation = OperationSpec::fromSwagger('get', '/domains/{id}', [
            'operationId' => 'GetDomain',
            'parameters' => [
                ['name' => 'id', 'in' => 'path', 'required' => true, 'type' => 'integer', 'format' => 'int32'],
                ['name' => 'with_history', 'in' => 'query', 'type' => 'boolean', 'format' => 'boolean'],
                ['name' => 'status', 'in' => 'query', 'type' => 'array', 'items' => ['type' => 'string'], 'collectionFormat' => 'multi'],
            ],
        ]);

        self::assertSame('GET', $operation->method);
        self::assertSame('/domains/{id}', $operation->path);
        self::assertSame('GetDomain', $operation->operationId);
        self::assertTrue($operation->requiresAuth);
        self::assertFalse($operation->isWrite());
        self::assertSame(['id', 'with_history', 'status'], array_map(static fn ($p) => $p->name, $operation->parameters));
        $status = $operation->parameter('status');
        self::assertNotNull($status);
        self::assertSame(ParameterLocation::Query, $status->in);
        self::assertSame('array', $status->type);
        self::assertSame('string', $status->itemType);
        self::assertSame('multi', $status->collectionFormat);
    }

    public function testAnEmptySecurityListMakesTheOperationPublic(): void
    {
        $operation = OperationSpec::fromSwagger('post', '/resellers/signup', ['operationId' => 'CreateResellerPublic', 'security' => []]);

        self::assertFalse($operation->requiresAuth);
        self::assertTrue($operation->isWrite());
    }

    public function testRejectsAPathPlaceholderWithoutAParameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('{id}');

        OperationSpec::fromSwagger('get', '/domains/{id}', ['operationId' => 'GetDomain']);
    }

    public function testRejectsParameterLocationsTheEncoderDoesNotSupport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('formData');

        OperationSpec::fromSwagger('post', '/x', ['operationId' => 'X', 'parameters' => [['name' => 'f', 'in' => 'formData', 'type' => 'string']]]);
    }

    public function testRejectsTwoBodies(): void
    {
        $this->expectException(InvalidArgumentException::class);

        OperationSpec::fromSwagger('post', '/x', ['operationId' => 'X', 'parameters' => [
            ['name' => 'a', 'in' => 'body', 'schema' => []],
            ['name' => 'b', 'in' => 'body', 'schema' => []],
        ]]);
    }

    public function testEveryOperationOfTheMergedSpecBuilds(): void
    {
        $spec = Json::object(Json::decode((string) file_get_contents(__DIR__ . '/../../spec/openprovider.json')), 'spec');
        $built = 0;
        $public = [];
        foreach (Json::object($spec['paths'], 'paths') as $path => $item) {
            foreach (Json::object($item, (string) $path) as $method => $operation) {
                $op = OperationSpec::fromSwagger((string) $method, (string) $path, Json::object($operation, 'operation'));
                ++$built;
                if (!$op->requiresAuth) {
                    $public[] = $op->method . ' ' . $op->path;
                }
            }
        }

        self::assertSame(140, $built);
        self::assertSame(['POST /resellers/signup'], $public);
    }
}
