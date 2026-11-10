<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Service;

use Ashio\OpenProvider\Auth\Credentials;
use Ashio\OpenProvider\Client\ApiClient;
use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Exception\NotFoundException;
use Ashio\OpenProvider\Exception\UnexpectedResponseException;
use Ashio\OpenProvider\Model\Domain\AdditionalDataSpecification;
use Ashio\OpenProvider\Model\ResellerCustomer\ResellerReseller;
use Ashio\OpenProvider\OpenProvider;
use Ashio\OpenProvider\Tests\Support\FakeHttpClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * How a service reads `data`: tolerant of absent data and unknown keys, explicit about data of
 * another JSON type than the spec declares, and never hiding the envelope's warnings.
 */
final class ResponseReadingTest extends TestCase
{
    private FakeHttpClient $http;
    private OpenProvider $openProvider;

    protected function setUp(): void
    {
        $this->http = (new FakeHttpClient())->login();
        $this->openProvider = new OpenProvider(ApiClient::create(ClientConfig::sandbox(), new Credentials('reseller', 'pw'), null, $this->http));
    }

    public function testAbsentDataReadsAsAnEmptyModelAndWarningsSurvive(): void
    {
        $this->http->reply(['code' => 0, 'desc' => 'ok', 'warnings' => [['code' => 1101, 'desc' => 'Check the handle']]], 201);

        $response = $this->openProvider->resellerCustomer()->getReseller();

        self::assertEquals(new ResellerReseller(), $response->data);
        self::assertSame([1101, 'Check the handle'], [$response->warnings[0]->code ?? null, $response->warnings[0]->desc ?? null]);
        self::assertSame([0, 'ok', 201], [$response->code, $response->desc, $response->httpStatus]);
    }

    public function testUnknownKeysStayInExtra(): void
    {
        $this->http->reply(['code' => 0, 'data' => ['id' => 7, 'brand_new_field' => ['x' => 1]]]);

        $reseller = $this->openProvider->resellerCustomer()->getReseller()->data;

        self::assertSame(7, $reseller->id);
        self::assertSame(['brand_new_field' => ['x' => 1]], $reseller->extra);
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function dataThatIsNotAnObject(): iterable
    {
        yield 'boolean' => [true];
        yield 'string' => ['done'];
        yield 'number' => [3];
        yield 'a list of strings' => [['x', 'y']];
        // A listing's results sent without their {results, total} object: reading it as an empty
        // object would end a Paginator walk at once, as if there were nothing to list.
        yield 'a list of objects' => [[['id' => 1], ['id' => 2]]];
    }

    public function testAnEmptyArrayIsAnEmptyObject(): void
    {
        // PHP decodes {} and [] alike; an empty answer is an empty model, not an error.
        $this->http->reply(['code' => 0, 'data' => []]);

        self::assertSame([], $this->openProvider->resellerCustomer()->getReseller()->data->toArray());
    }

    #[DataProvider('dataThatIsNotAnObject')]
    public function testDataOfAnotherTypeIsAnExceptionThatSaysTheCallSucceeded(mixed $data): void
    {
        $this->http->reply(['code' => 0, 'data' => $data]);

        try {
            $this->openProvider->resellerCustomer()->getReseller();
            self::fail('expected an exception');
        } catch (UnexpectedResponseException $e) {
            self::assertStringContainsString('reported success (code 0)', $e->getMessage());
            self::assertStringContainsString(ResellerReseller::class, $e->getMessage());
            self::assertSame(200, $e->httpStatus);
        }
    }

    public function testAListResponseReadsEachItem(): void
    {
        $this->http->reply(['code' => 0, 'data' => [['name' => 'a'], ['name' => 'b']]]);

        $items = $this->openProvider->domain()->getAdditionalData()->data;

        self::assertCount(2, $items);
        self::assertSame(['a', 'b'], [$items[0]->name ?? null, $items[1]->name ?? null]);
    }

    public function testAbsentListDataReadsAsAnEmptyList(): void
    {
        $this->http->reply(['code' => 0]);

        self::assertSame([], $this->openProvider->domain()->getAdditionalData()->data);
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function dataThatIsNotAListOfObjects(): iterable
    {
        yield 'an object' => [['name' => 'a']];
        yield 'a list of scalars' => [['a', 'b']];
        yield 'a scalar' => ['a'];
    }

    #[DataProvider('dataThatIsNotAListOfObjects')]
    public function testAListOfAnotherShapeIsAnException(mixed $data): void
    {
        $this->http->reply(['code' => 0, 'data' => $data]);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('a list of objects of ' . AdditionalDataSpecification::class);

        $this->openProvider->domain()->getAdditionalData();
    }

    public function testAnErrorCodeIsThrownBeforeAnyReading(): void
    {
        $this->http->reply(['code' => 201, 'desc' => 'Entity not found', 'data' => 'irrelevant'], 500);

        $this->expectException(NotFoundException::class);

        $this->openProvider->resellerCustomer()->getReseller();
    }
}
