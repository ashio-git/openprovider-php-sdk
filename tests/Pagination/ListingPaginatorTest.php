<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Pagination;

use Ashio\OpenProvider\Auth\Credentials;
use Ashio\OpenProvider\Client\AbstractService;
use Ashio\OpenProvider\Client\ApiClient;
use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Exception\InvalidArgumentException;
use Ashio\OpenProvider\Exception\UnexpectedResponseException;
use Ashio\OpenProvider\Model\Dns\ZoneZone;
use Ashio\OpenProvider\Model\ResellerCustomer\PersoncontactContact;
use Ashio\OpenProvider\OpenProvider;
use Ashio\OpenProvider\Pagination\Paginator;
use Ashio\OpenProvider\Spec\Operation;
use Ashio\OpenProvider\Tests\Support\FakeHttpClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A9: Paginator::all() walks a service's list method page by page at the endpoint's documented
 * maximum (spec/behavior.json through the operation registry), or at the API's default of 100
 * when none is documented, and yields the models of `results`.
 */
final class ListingPaginatorTest extends TestCase
{
    private FakeHttpClient $http;
    private OpenProvider $openProvider;

    protected function setUp(): void
    {
        $this->http = (new FakeHttpClient())->login();
        $this->openProvider = new OpenProvider(ApiClient::create(ClientConfig::sandbox(), new Credentials('reseller', 'pw'), null, $this->http));
    }

    public function testItPagesAtTheEndpointsDocumentedMaximumAndForwardsTheFilters(): void
    {
        $this->page(self::zones(1, 500), 501)->page(self::zones(501, 1), 501);

        $zones = iterator_to_array(Paginator::all($this->openProvider->dns()->listZones(...), ['namePattern' => 'ex*']), false);

        self::assertCount(501, $zones);
        self::assertContainsOnlyInstancesOf(ZoneZone::class, $zones);
        self::assertSame(['z1', 'z501'], [$zones[0]->name, $zones[500]->name]);
        self::assertSame([
            'GET /v1/dns/zones?limit=500&offset=0&name_pattern=ex%2A',
            'GET /v1/dns/zones?limit=500&offset=500&name_pattern=ex%2A',
        ], array_slice($this->http->targets(), 1));
    }

    public function testWithoutADocumentedMaximumItUsesTheApiDefault(): void
    {
        $this->page(self::contacts(100), 150)->page(self::contacts(50), 150);

        $contacts = iterator_to_array(Paginator::all($this->openProvider->resellerCustomer()->listContacts(...)), false);

        self::assertCount(150, $contacts);
        self::assertContainsOnlyInstancesOf(PersoncontactContact::class, $contacts);
        self::assertSame(['GET /v1/contacts?limit=100&offset=0', 'GET /v1/contacts?limit=100&offset=100'], array_slice($this->http->targets(), 1));
    }

    public function testAPageSizeLowersTheLimitButNeverRaisesItAboveTheMaximum(): void
    {
        $this->page([], 0)->page([], 0)->page([], 0);

        iterator_to_array(Paginator::all($this->openProvider->dns()->listZones(...), pageSize: 50));
        iterator_to_array(Paginator::all($this->openProvider->dns()->listZones(...), pageSize: 5000));
        iterator_to_array(Paginator::all($this->openProvider->resellerCustomer()->listContacts(...), pageSize: 2500));

        self::assertSame([
            'GET /v1/dns/zones?limit=50&offset=0',
            'GET /v1/dns/zones?limit=500&offset=0',
            'GET /v1/contacts?limit=2500&offset=0',
        ], array_slice($this->http->targets(), 1));
    }

    public function testATotalThatIsNotAnIntegerIsIgnored(): void
    {
        $this->http->reply(['code' => 0, 'data' => ['results' => self::zones(1, 1), 'total' => '1']]);
        $this->http->reply(['code' => 0, 'data' => ['results' => [], 'total' => '1']]);

        $zones = iterator_to_array(Paginator::all($this->openProvider->dns()->listZones(...), pageSize: 1), false);

        self::assertCount(1, $zones);
        self::assertSame(['GET /v1/dns/zones?limit=1&offset=0', 'GET /v1/dns/zones?limit=1&offset=1'], array_slice($this->http->targets(), 1));
    }

    public function testAServiceMethodWithoutAnOperationIsRefused(): void
    {
        $service = new class ($this->openProvider->client()) extends AbstractService {
            public function helper(): null
            {
                return null;
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Paginator::all() takes a service\'s list method');

        Paginator::all($service->helper(...));
    }

    public function testAbsentResultsEndTheWalk(): void
    {
        $this->http->reply(['code' => 0, 'data' => ['total' => 0]]);

        self::assertSame([], iterator_to_array(Paginator::all($this->openProvider->dns()->listZones(...))));
    }

    public function testResultsOfAnotherTypeAreAnErrorNotAnEmptyPage(): void
    {
        $this->http->reply(['code' => 0, 'data' => ['results' => 'none', 'total' => 3]]);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('ListZones returned "results" that is not a list of models');

        iterator_to_array(Paginator::all($this->openProvider->dns()->listZones(...)));
    }

    public function testAListMethodThatReturnsNoModelIsALogicError(): void
    {
        $service = new class ($this->openProvider->client()) extends AbstractService {
            /**
             * @return list<never>
             */
            #[Operation('GET', '/dns/zones', 'ListZones')]
            public function listZones(?int $limit = null, ?int $offset = null): array
            {
                return [];
            }
        };

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('ListZones did not return an ApiResponse holding a model');

        iterator_to_array(Paginator::all($service->listZones(...)));
    }

    /**
     * @return iterable<string, array{\Closure(OpenProvider): void, string}>
     */
    public static function refusedCalls(): iterable
    {
        yield 'a closure that is no service method' => [
            static function (OpenProvider $op): void {
                Paginator::all(static fn (): null => null);
            },
            'Paginator::all() takes a service\'s list method as a first-class callable',
        ];
        yield 'an operation that is not a paged listing' => [
            static function (OpenProvider $op): void {
                Paginator::all($op->domain()->getTld(...), ['name' => 'com']);
            },
            'GetTld is not a paged listing',
        ];
        yield 'limit set by the caller' => [
            static function (OpenProvider $op): void {
                Paginator::all($op->dns()->listZones(...), ['limit' => 5]);
            },
            'Paginator::all() sets limit and offset itself',
        ];
        yield 'an argument the method does not take' => [
            static function (OpenProvider $op): void {
                Paginator::all($op->dns()->listZones(...), ['name_pattern' => 'x']);
            },
            'listZones() has no parameter $name_pattern',
        ];
        yield 'a page size below one' => [
            static function (OpenProvider $op): void {
                Paginator::all($op->dns()->listZones(...), pageSize: 0);
            },
            'pageSize must be at least 1',
        ];
    }

    /**
     * @param \Closure(OpenProvider): void $call
     */
    #[DataProvider('refusedCalls')]
    public function testMisuseIsRefusedBeforeAnyRequest(\Closure $call, string $message): void
    {
        try {
            $call($this->openProvider);
            self::fail('no exception');
        } catch (InvalidArgumentException $e) {
            self::assertStringContainsString($message, $e->getMessage());
        }
        self::assertSame([], $this->http->targets(), 'nothing is sent, not even the login');
    }

    /**
     * @param list<array<string, mixed>> $results
     */
    private function page(array $results, int $total): self
    {
        $this->http->reply(['code' => 0, 'data' => ['results' => $results, 'total' => $total]]);

        return $this;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function zones(int $from, int $count): array
    {
        return array_map(static fn (int $i): array => ['name' => 'z' . $i], range($from, $from + $count - 1));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function contacts(int $count): array
    {
        return array_fill(0, $count, ['email' => 'a@example.com']);
    }
}
