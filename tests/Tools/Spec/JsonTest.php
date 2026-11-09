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
}
