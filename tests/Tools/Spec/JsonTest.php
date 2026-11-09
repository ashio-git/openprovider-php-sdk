<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Spec;

use Ashio\OpenProvider\Tools\Spec\Json;
use Ashio\OpenProvider\Tools\Spec\SpecException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class JsonTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function documentsThatLoseShapeWithAssocDecoding(): iterable
    {
        yield 'empty object' => ['{"properties":{}}'];
        yield 'empty list' => ['{"security":[]}'];
        yield 'list of empty object' => ['{"security":[{"Bearer":[]}]}'];
        yield 'object with list-like keys' => ['{"example":{"0":"a","1":"b"}}'];
        yield 'float with zero fraction' => ['{"default":1.0}'];
        yield 'unicode and slashes' => ['{"description":"café / naïve"}'];
        yield 'nested empty object in list' => ['[{},{"a":{}}]'];
    }

    #[DataProvider('documentsThatLoseShapeWithAssocDecoding')]
    public function testDecodeThenEncodeKeepsTheDocument(string $document): void
    {
        $roundTrip = Json::encode(Json::decode($document));

        self::assertEquals(json_decode($document), json_decode($roundTrip));
        self::assertSame(json_encode(json_decode($document)), json_encode(json_decode($roundTrip)));
    }

    public function testDecodedObjectsAreArraysWhenTheyCanBeEncodedBackAsObjects(): void
    {
        $decoded = Json::decode('{"a":{"b":1},"c":{}}');

        self::assertIsArray($decoded);
        self::assertSame(['b' => 1], $decoded['a']);
        self::assertInstanceOf(\stdClass::class, $decoded['c']);
    }

    public function testEncodingIsStableAndEndsWithANewline(): void
    {
        $value = ['b' => [1, 2], 'a' => 'é/x'];

        $first = Json::encode($value);

        self::assertSame($first, Json::encode(Json::decode($first)));
        self::assertStringEndsWith("}\n", $first);
        self::assertStringContainsString('"é/x"', $first);
    }

    public function testInvalidJsonIsReportedWithItsOrigin(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('auth.swagger.json');

        Json::decode('{"a":', 'auth.swagger.json');
    }

    public function testObjectRequiresAnObject(): void
    {
        self::assertSame(['a' => 1], Json::object(['a' => 1], 'ctx'));
        self::assertSame([], Json::object(new \stdClass(), 'ctx'));

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('ctx');
        Json::object([1, 2], 'ctx');
    }

    public function testTypedCollectionsCheckEveryEntry(): void
    {
        $empty = new \stdClass();

        self::assertSame(['/a' => ['get' => []], '/b' => []], Json::objectMap(['/a' => ['get' => []], '/b' => $empty], 'paths'));
        self::assertSame([['name' => 'x'], []], Json::objectList([['name' => 'x'], $empty], 'parameters'));
        self::assertSame(['a', 'b'], Json::stringList(['a', 'b'], 'tags'));
        self::assertSame([], Json::objectMap([], 'paths'));
        self::assertSame([], Json::stringList([], 'tags'));
    }

    /**
     * @return iterable<string, array{\Closure(): mixed, string}>
     */
    public static function badCollections(): iterable
    {
        yield 'map with a string value' => [static fn (): mixed => Json::objectMap(['/a' => 'x'], 'paths'), 'paths./a must be a JSON object, got string'];
        yield 'map that is a list' => [static fn (): mixed => Json::objectMap([[]], 'paths'), 'paths must be a JSON object'];
        yield 'list with a list item' => [static fn (): mixed => Json::objectList([['a' => 1], [1]], 'parameters'), 'parameters[1] must be a JSON object, got array'];
        yield 'list that is a map' => [static fn (): mixed => Json::objectList(['a' => []], 'parameters'), 'parameters must be a JSON array'];
        yield 'list with a number' => [static fn (): mixed => Json::stringList(['a', 2], 'tags'), 'tags[1] must be a string, got int'];
    }

    #[DataProvider('badCollections')]
    public function testTypedCollectionsNameTheBadEntry(\Closure $read, string $message): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage($message);

        $read();
    }

    public function testOptionalStringIsNullForAbsentOrOtherTypes(): void
    {
        $object = ['title' => 'T', 'count' => 3, 'none' => null];

        self::assertSame('T', Json::optionalString($object, 'title'));
        self::assertNull(Json::optionalString($object, 'count'));
        self::assertNull(Json::optionalString($object, 'none'));
        self::assertNull(Json::optionalString($object, 'missing'));
    }

    public function testValueAcceptsWhatJsonHoldsAndNothingElse(): void
    {
        $object = new \stdClass();
        foreach ([null, true, 0, 1.5, '', [], ['a' => 1], $object] as $value) {
            self::assertSame($value, Json::value($value, 'ctx'));
        }

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('ctx must be a JSON value, got Closure');
        Json::value(static fn (): null => null, 'ctx');
    }

    public function testMapValuesKeepsKeysAndOrderAndPassesTheKey(): void
    {
        $mapped = Json::mapValues(['b' => 1, 'a' => 2, 7 => 3], static fn (mixed $value, int|string $key): string => $key . '=' . (is_int($value) ? $value : 0));

        self::assertSame(['b' => 'b=1', 'a' => 'a=2', 7 => '7=3'], $mapped);
        self::assertSame([], Json::mapValues([], static fn (mixed $value): mixed => $value));
    }
}
