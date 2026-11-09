<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Response;

use Ashio\OpenProvider\Exception\UnexpectedResponseException;
use Ashio\OpenProvider\Response\ApiResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ApiResponseTest extends TestCase
{
    public function testReadsTheWholeEnvelope(): void
    {
        $response = ApiResponse::fromHttp(200, (string) json_encode([
            'code' => 0,
            'desc' => '',
            'data' => ['id' => 5, 'active' => false, 'count' => 0],
            'warnings' => [['code' => 1001, 'desc' => 'Check the handle', 'data' => 'x']],
            'maintenance' => true,
        ]));

        self::assertTrue($response->isSuccess());
        self::assertSame(0, $response->code);
        self::assertSame(['id' => 5, 'active' => false, 'count' => 0], $response->data);
        self::assertCount(1, $response->warnings);
        self::assertSame(1001, $response->warnings[0]->code);
        self::assertSame('Check the handle', $response->warnings[0]->desc);
        self::assertSame('x', $response->warnings[0]->data);
        self::assertTrue($response->maintenance);
        self::assertSame(200, $response->httpStatus);
    }

    public function testTheBodyCodeDecidesSuccessWhateverTheHttpStatus(): void
    {
        $failure = ApiResponse::fromHttp(200, '{"code":196,"desc":"Authentication/Authorization Failed","data":""}');
        $success = ApiResponse::fromHttp(500, '{"code":0,"data":{}}');

        self::assertFalse($failure->isSuccess());
        self::assertSame(196, $failure->code);
        self::assertTrue($success->isSuccess());
        self::assertSame(500, $success->httpStatus);
    }

    public function testMissingOptionalPartsHaveNeutralValues(): void
    {
        $response = ApiResponse::fromHttp(200, '{"code":0}');

        self::assertSame('', $response->desc);
        self::assertNull($response->data);
        self::assertSame([], $response->warnings);
        self::assertFalse($response->maintenance);
    }

    public function testMalformedWarningsDoNotHideTheResponse(): void
    {
        $response = ApiResponse::fromHttp(200, '{"code":0,"warnings":[{"desc":"no code"},"text",{"code":"12","desc":3}]}');

        self::assertCount(2, $response->warnings);
        self::assertSame(0, $response->warnings[0]->code);
        self::assertSame('no code', $response->warnings[0]->desc);
        self::assertSame(12, $response->warnings[1]->code);
        self::assertSame('', $response->warnings[1]->desc);
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function bodiesThatAreNotAnEnvelope(): iterable
    {
        yield 'html from a proxy' => [502, '<html>Bad gateway</html>'];
        yield 'empty body' => [500, ''];
        yield 'truncated json' => [200, '{"code":0,'];
        yield 'json list' => [200, '[1,2]'];
        yield 'no code' => [200, '{"desc":"x"}'];
        yield 'code is not an integer' => [200, '{"code":"0"}'];
    }

    public function testWithDataReplacesOnlyTheData(): void
    {
        $response = ApiResponse::fromHttp(201, (string) json_encode([
            'code' => 0,
            'desc' => 'ok',
            'data' => ['id' => 5],
            'warnings' => [['code' => 1001, 'desc' => 'Check the handle']],
            'maintenance' => true,
        ]));

        $typed = $response->withData(new \ArrayObject(['id' => 5]));

        self::assertNotSame($response, $typed);
        self::assertEquals(new \ArrayObject(['id' => 5]), $typed->data);
        self::assertSame(['id' => 5], $response->data);
        self::assertSame([0, 'ok', true, 201], [$typed->code, $typed->desc, $typed->maintenance, $typed->httpStatus]);
        self::assertSame($response->warnings, $typed->warnings);
    }

    #[DataProvider('bodiesThatAreNotAnEnvelope')]
    public function testABodyThatIsNotAnEnvelopeIsAnException(int $status, string $body): void
    {
        try {
            ApiResponse::fromHttp($status, $body);
            self::fail('expected an exception');
        } catch (UnexpectedResponseException $e) {
            self::assertSame($status, $e->httpStatus);
            self::assertStringContainsString((string) $status, $e->getMessage());
        }
    }
}
