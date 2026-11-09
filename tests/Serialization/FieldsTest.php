<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Serialization;

use Ashio\OpenProvider\Serialization\Fields;
use Ashio\OpenProvider\Tests\Support\SampleModel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The tolerant hydration rules every generated model relies on: a value of the declared type
 * leaves $rest and is returned; anything else (absent, null, another type) stays in $rest as
 * received, so toArray() can give it back unchanged.
 */
final class FieldsTest extends TestCase
{
    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function accepted(): iterable
    {
        yield 'string' => ['string', 'abc'];
        yield 'empty string' => ['string', ''];
        yield 'int' => ['int', 7];
        yield 'zero int' => ['int', 0];
        yield 'number as int' => ['number', 3];
        yield 'number as float' => ['number', 2.5];
        yield 'zero float' => ['number', 0.0];
        yield 'true' => ['bool', true];
        yield 'false' => ['bool', false];
        yield 'strings' => ['strings', ['a', '']];
        yield 'no strings' => ['strings', []];
        yield 'ints' => ['ints', [0, -1]];
        yield 'numbers' => ['numbers', [1, 1.5]];
        yield 'bools' => ['bools', [false, true]];
        yield 'string map' => ['stringMap', ['a' => 'x']];
        yield 'int map' => ['intMap', ['a' => 0]];
        yield 'number map' => ['numberMap', ['a' => 0.5, 'b' => 1]];
        yield 'bool map' => ['boolMap', ['sku' => false]];
        yield 'bool map with numeric keys' => ['boolMap', [0 => true, 1 => false]];
        yield 'empty map' => ['boolMap', []];
    }

    #[DataProvider('accepted')]
    public function testAValueOfTheDeclaredTypeIsTakenOutOfTheRest(string $kind, mixed $value): void
    {
        $rest = ['k' => $value, 'other' => 1];

        self::assertSame($value, self::take($kind, $rest));
        self::assertSame(['other' => 1], $rest);
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function rejected(): iterable
    {
        yield 'int for string' => ['string', 1];
        yield 'numeric string for int' => ['int', '1'];
        yield 'float for int' => ['int', 1.0];
        yield 'string for number' => ['number', '1.5'];
        yield 'infinite number' => ['number', INF];
        yield 'int for bool' => ['bool', 1];
        yield 'string for bool' => ['bool', 'true'];
        yield 'map for strings' => ['strings', ['a' => 'x']];
        yield 'mixed list for strings' => ['strings', ['a', 1]];
        yield 'scalar for ints' => ['ints', 1];
        yield 'string in numbers' => ['numbers', [1, '2']];
        yield 'int in bools' => ['bools', [true, 0]];
        yield 'int value in bool map' => ['boolMap', ['a' => 1]];
        yield 'scalar for map' => ['stringMap', 'x'];
        yield 'string in int map' => ['intMap', ['a' => '1']];
        yield 'bool in number map' => ['numberMap', ['a' => true]];
        yield 'int in string map' => ['stringMap', ['a' => 1]];
    }

    #[DataProvider('rejected')]
    public function testAValueOfAnotherTypeStaysInTheRestAsReceived(string $kind, mixed $value): void
    {
        $rest = ['k' => $value];

        self::assertNull(self::take($kind, $rest));
        self::assertSame(['k' => $value], $rest);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function kinds(): iterable
    {
        foreach (['string', 'int', 'number', 'bool', 'strings', 'ints', 'numbers', 'bools', 'stringMap', 'intMap', 'numberMap', 'boolMap', 'object', 'objects', 'objectMap'] as $kind) {
            yield $kind => [$kind];
        }
    }

    #[DataProvider('kinds')]
    public function testAnAbsentKeyIsNullAndAnExplicitNullStaysInTheRest(string $kind): void
    {
        $rest = ['other' => 1];
        self::assertNull(self::take($kind, $rest));
        self::assertSame(['other' => 1], $rest);

        $rest = ['k' => null];
        self::assertNull(self::take($kind, $rest));
        self::assertSame(['k' => null], $rest);
    }

    public function testAnObjectIsBuiltFromAMapOrAnEmptyArray(): void
    {
        $rest = ['k' => ['id' => 0, 'x' => 'y']];
        $model = Fields::object($rest, 'k', SampleModel::class);
        self::assertInstanceOf(SampleModel::class, $model);
        self::assertSame(0, $model->id);
        self::assertSame(['x' => 'y'], $model->extra);
        self::assertSame([], $rest);

        $rest = ['k' => []];
        $model = Fields::object($rest, 'k', SampleModel::class);
        self::assertInstanceOf(SampleModel::class, $model);
        self::assertSame([], $model->toArray());

        $rest = ['k' => [1, 2]];
        self::assertNull(Fields::object($rest, 'k', SampleModel::class));
        self::assertSame(['k' => [1, 2]], $rest);
    }

    public function testObjectListsAndMapsNeedEveryItemToBeAMap(): void
    {
        $rest = ['k' => [['id' => 1], []]];
        $list = Fields::objects($rest, 'k', SampleModel::class);
        self::assertNotNull($list);
        self::assertSame([1, null], array_map(static fn (SampleModel $m): ?int => $m->id, $list));

        $rest = ['k' => [['id' => 1], 'x']];
        self::assertNull(Fields::objects($rest, 'k', SampleModel::class));
        self::assertSame(['k' => [['id' => 1], 'x']], $rest);

        $rest = ['k' => ['id' => 1]];
        self::assertNull(Fields::objects($rest, 'k', SampleModel::class), 'a single map is not a list');

        $rest = ['k' => ['a' => ['id' => 2]]];
        $map = Fields::objectMap($rest, 'k', SampleModel::class);
        self::assertNotNull($map);
        self::assertSame(2, $map['a']->id);

        $rest = ['k' => ['a' => 5]];
        self::assertNull(Fields::objectMap($rest, 'k', SampleModel::class));
        self::assertSame(['k' => ['a' => 5]], $rest);
    }

    public function testToArrayDropsTypedNullsKeepsFalsyValuesAndAppendsTheRest(): void
    {
        $nested = new SampleModel(id: 0, extra: ['n' => null]);

        self::assertSame(
            ['a' => 0, 'b' => false, 'c' => '', 'd' => [], 'e' => ['id' => 0, 'n' => null], 'f' => [['id' => 0, 'n' => null]], 'g' => ['x' => ['id' => 0, 'n' => null]], 'z' => null],
            Fields::toArray(
                ['a' => 0, 'b' => false, 'c' => '', 'd' => [], 'e' => $nested, 'f' => [$nested], 'g' => ['x' => $nested], 'h' => null],
                ['z' => null],
            ),
        );
    }

    public function testATypedValueWinsOverTheSameKeyInTheRest(): void
    {
        self::assertSame(['a' => 1], Fields::toArray(['a' => 1], ['a' => 'raw']));
        self::assertSame(['a' => 'raw'], Fields::toArray(['a' => null], ['a' => 'raw']));
    }

    public function testJsonIsAlwaysAnObjectAndKeepsNestedModelsAndMapsAsObjects(): void
    {
        $flags = JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR;

        self::assertSame('{}', json_encode(Fields::toJson([], []), $flags));
        self::assertSame('{}', json_encode(Fields::toJson(['a' => null], []), $flags));
        self::assertSame('{"0":"x"}', json_encode(Fields::toJson([], [0 => 'x']), $flags));
        self::assertSame(
            '{"m":{},"n":{"0":true},"o":{},"l":[{}],"x":0.0,"r":[]}',
            json_encode(Fields::toJson(['m' => [], 'n' => [0 => true], 'o' => new SampleModel(), 'l' => [new SampleModel()], 'x' => 0.0], ['r' => []], ['m', 'n']), $flags),
        );
    }

    /**
     * @param array<array-key, mixed> $rest
     */
    private static function take(string $kind, array &$rest): mixed
    {
        return match ($kind) {
            'string' => Fields::string($rest, 'k'),
            'int' => Fields::int($rest, 'k'),
            'number' => Fields::number($rest, 'k'),
            'bool' => Fields::bool($rest, 'k'),
            'strings' => Fields::strings($rest, 'k'),
            'ints' => Fields::ints($rest, 'k'),
            'numbers' => Fields::numbers($rest, 'k'),
            'bools' => Fields::bools($rest, 'k'),
            'stringMap' => Fields::stringMap($rest, 'k'),
            'intMap' => Fields::intMap($rest, 'k'),
            'numberMap' => Fields::numberMap($rest, 'k'),
            'boolMap' => Fields::boolMap($rest, 'k'),
            'object' => Fields::object($rest, 'k', SampleModel::class),
            'objects' => Fields::objects($rest, 'k', SampleModel::class),
            'objectMap' => Fields::objectMap($rest, 'k', SampleModel::class),
            default => throw new \LogicException($kind),
        };
    }
}
