<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Conformance;

use Ashio\OpenProvider\Tools\Conformance\SpecSamples;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The values the audit and the contract tests probe with. Both compute their expectations from
 * these samples, so a wrong sample would cancel out there: the values are pinned here instead.
 */
final class SpecSamplesTest extends TestCase
{
    public function testAnObjectIsItsExampleWhenItHasOne(): void
    {
        self::assertSame(['id' => 99], self::samples()->object('withExample'));
    }

    public function testAnObjectWithoutExampleIsBuiltFromItsPropertiesByType(): void
    {
        self::assertSame([
            'count' => 7,
            'ratio' => 2.5,
            'active' => true,
            'name' => 'sample',
            'untyped' => 'sample',
            'tags' => ['sample'],
            'kind' => 'first',
            'fixed' => 'given',
            'status' => 'ACT',
            'child' => ['id' => 99],
            'labels' => ['key' => 'sample'],
            'free' => [],
            'nested' => ['inner' => 7],
        ], self::samples()->object('built'));
    }

    public function testTypedIgnoresTheExample(): void
    {
        self::assertSame(['id' => 7], self::samples()->typed('withExample'));
    }

    public function testNestingStopsAtTheMaximumDepth(): void
    {
        // Each level (a $ref object, an array item, a map value) adds one; a container at depth 3
        // or more is empty, so a self-referencing definition still ends.
        self::assertSame(
            '{"next":{"next":{"next":{"next":[],"list":[],"map":[]},"list":[[]],"map":{"key":[]}},"list":[[[]]],"map":{"key":{"key":[]}}},'
            . '"list":[[[[]]]],"map":{"key":{"key":{"key":7}}}}',
            json_encode(self::samples()->object('loop'), JSON_THROW_ON_ERROR),
        );
    }

    public function testEnvelopeDataIsTheEnvelopesExampleOrASampleOfItsDataSchema(): void
    {
        $samples = self::samples();

        self::assertSame(['from' => 'example'], $samples->data('envelopeWithExample'));
        self::assertNull($samples->data('envelopeWithNullData'));
        self::assertSame([['id' => 99]], $samples->data('envelopeWithList'));
        self::assertSame('sample', $samples->data('envelopeWithoutData'));
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>, int, mixed}>
     */
    public static function parameters(): iterable
    {
        yield 'integer' => [['type' => 'integer'], 3, 43];
        yield 'number' => [['type' => 'number'], 3, 4.5];
        yield 'boolean, even seed' => [['type' => 'boolean'], 2, false];
        yield 'boolean, odd seed' => [['type' => 'boolean'], 3, true];
        yield 'string' => [['type' => 'string'], 3, 'v3 ä/&='];
        yield 'no type' => [[], 0, 'v0 ä/&='];
        yield 'enum, by seed' => [['type' => 'string', 'enum' => ['a', 'b', 'c']], 4, 'b'];
        yield 'enum with keys' => [['type' => 'string', 'enum' => ['x' => 'a', 'y' => 'b']], 1, 'b'];
        yield 'empty enum' => [['type' => 'integer', 'enum' => []], 1, 41];
        yield 'array, two seeds' => [['type' => 'array', 'items' => ['type' => 'integer']], 5, [45, 46]];
        yield 'array without items' => [['type' => 'array'], 5, ['v5 ä/&=', 'v6 ä/&=']];
    }

    /**
     * @param array<array-key, mixed> $parameter
     */
    #[DataProvider('parameters')]
    public function testAParameterValueFollowsTypeEnumAndSeed(array $parameter, int $seed, mixed $expected): void
    {
        self::assertSame($expected, SpecSamples::parameter($parameter, $seed));
    }

    private static function samples(): SpecSamples
    {
        $ref = static fn (string $name): array => ['$ref' => '#/definitions/' . $name];

        return new SpecSamples([
            'withExample' => ['type' => 'object', 'example' => ['id' => 99], 'properties' => ['id' => ['type' => 'integer']]],
            'status' => ['type' => 'string', 'enum' => ['ACT', 'DEL']],
            'built' => ['type' => 'object', 'properties' => [
                'count' => ['type' => 'integer'],
                'ratio' => ['type' => 'number'],
                'active' => ['type' => 'boolean'],
                'name' => ['type' => 'string'],
                'untyped' => [],
                'tags' => ['type' => 'array', 'items' => ['type' => 'string']],
                'kind' => ['type' => 'string', 'enum' => ['first', 'second']],
                'fixed' => ['type' => 'string', 'example' => 'given'],
                'status' => $ref('status'),
                'child' => $ref('withExample'),
                'labels' => ['type' => 'object', 'additionalProperties' => ['type' => 'string']],
                'free' => ['type' => 'object'],
                'nested' => ['type' => 'object', 'properties' => ['inner' => ['type' => 'integer']]],
            ]],
            'loop' => ['type' => 'object', 'properties' => [
                'next' => $ref('loop'),
                'list' => ['type' => 'array', 'items' => ['type' => 'array', 'items' => ['type' => 'array', 'items' => ['type' => 'array', 'items' => ['type' => 'integer']]]]],
                'map' => ['type' => 'object', 'additionalProperties' => ['type' => 'object', 'additionalProperties' => ['type' => 'object', 'additionalProperties' => ['type' => 'integer']]]],
            ]],
            'envelopeWithExample' => ['type' => 'object', 'example' => ['code' => 0, 'data' => ['from' => 'example']], 'properties' => ['data' => $ref('withExample')]],
            'envelopeWithNullData' => ['type' => 'object', 'example' => ['data' => null], 'properties' => ['data' => $ref('withExample')]],
            'envelopeWithList' => ['type' => 'object', 'example' => ['code' => 0], 'properties' => ['data' => ['type' => 'array', 'items' => $ref('withExample')]]],
            'envelopeWithoutData' => ['type' => 'object'],
        ]);
    }
}
