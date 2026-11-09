<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Generate;

use Ashio\OpenProvider\Tests\Support\ArrayPath;
use Ashio\OpenProvider\Tools\Generate\ModelSet;
use Ashio\OpenProvider\Tools\Spec\SpecException;
use PHPUnit\Framework\TestCase;

/**
 * Which definitions become models: everything reachable from a request body or from the `data`
 * of a success envelope. The envelopes themselves, the error schemas and their warnings are
 * read by the core (ApiResponse, ApiException, Warning), so they are not models.
 */
final class ModelSetTest extends TestCase
{
    public function testReachabilityStartsAtBodiesAndEnvelopeDataAndFollowsReferences(): void
    {
        $set = ModelSet::fromSpec(self::spec());

        self::assertSame(['d.Body', 'd.Item', 'd.Nested', 'd.Thing'], $set->models);
        self::assertSame(['d.Size'], $set->enums);
        self::assertSame(['d.GetThingResponse'], $set->envelopes);
        self::assertSame(['d.errorError'], $set->errors);
        self::assertSame(['d.errorWarning'], $set->warnings);
        self::assertSame(['d.Orphan'], $set->unreachable);
    }

    public function testADefinitionReachableFromDataIsAModelEvenIfAnEnvelopeAlsoUsesIt(): void
    {
        $spec = ArrayPath::with(self::spec(), ['$ref' => '#/definitions/d.errorWarning'], 'definitions', 'd.Thing', 'properties', 'warning');

        $set = ModelSet::fromSpec($spec);

        self::assertContains('d.errorWarning', $set->models);
        self::assertSame([], $set->warnings);
    }

    public function testAnEnvelopeFieldTheCoreDoesNotReadIsRefused(): void
    {
        $spec = ArrayPath::with(self::spec(), ['type' => 'integer'], 'definitions', 'd.GetThingResponse', 'properties', 'total');

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('d.GetThingResponse has "total"');

        ModelSet::fromSpec($spec);
    }

    public function testAnInlineSuccessSchemaIsRefused(): void
    {
        $spec = ArrayPath::with(self::spec(), ['type' => 'object'], 'paths', '/things/{id}', 'get', 'responses', '200', 'schema');

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('GetThing: the 200 response is not a $ref');

        ModelSet::fromSpec($spec);
    }

    public function testAReferenceToAMissingDefinitionIsRefused(): void
    {
        $spec = ArrayPath::with(self::spec(), ['$ref' => '#/definitions/d.Gone'], 'definitions', 'd.Nested', 'properties', 'gone');

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('d.Gone');

        ModelSet::fromSpec($spec);
    }

    /**
     * @return array<string, mixed>
     */
    public static function spec(): array
    {
        $ref = static fn (string $name): array => ['$ref' => '#/definitions/' . $name];

        return [
            'paths' => [
                '/things/{id}' => [
                    'parameters' => [['name' => 'id', 'in' => 'path', 'type' => 'integer']],
                    'get' => [
                        'operationId' => 'GetThing',
                        'responses' => ['200' => ['schema' => $ref('d.GetThingResponse')], 'default' => ['schema' => $ref('d.errorError')]],
                    ],
                    'put' => [
                        'operationId' => 'UpdateThing',
                        'parameters' => [['name' => 'body', 'in' => 'body', 'schema' => $ref('d.Body')]],
                        'responses' => ['200' => ['schema' => $ref('d.GetThingResponse')], 'default' => ['schema' => $ref('d.errorError')]],
                    ],
                ],
            ],
            'definitions' => [
                'd.GetThingResponse' => ['type' => 'object', 'properties' => [
                    'code' => ['type' => 'integer'],
                    'desc' => ['type' => 'string'],
                    'data' => $ref('d.Thing'),
                    'warnings' => ['type' => 'array', 'items' => $ref('d.errorWarning')],
                    'maintenance' => ['type' => 'boolean'],
                ]],
                'd.errorError' => ['type' => 'object', 'properties' => ['code' => ['type' => 'integer'], 'data' => ['type' => 'string'], 'desc' => ['type' => 'string']]],
                'd.errorWarning' => ['type' => 'object', 'properties' => ['code' => ['type' => 'integer'], 'data' => ['type' => 'string'], 'desc' => ['type' => 'string']]],
                'd.Thing' => ['type' => 'object', 'properties' => ['nested' => $ref('d.Nested'), 'items' => ['type' => 'array', 'items' => $ref('d.Item')]]],
                'd.Nested' => ['type' => 'object', 'properties' => ['size' => $ref('d.Size')]],
                'd.Item' => ['type' => 'object', 'properties' => ['id' => ['type' => 'integer']]],
                'd.Size' => ['type' => 'string', 'enum' => ['S', 'M']],
                'd.Body' => ['type' => 'object', 'properties' => ['item' => $ref('d.Item')]],
                'd.Orphan' => ['type' => 'object', 'properties' => []],
            ],
        ];
    }
}
