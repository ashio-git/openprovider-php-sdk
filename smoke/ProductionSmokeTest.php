<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Smoke;

use Ashio\OpenProvider\Auth\Credentials;
use Ashio\OpenProvider\Client\ApiClient;
use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Exception\AuthenticationException;
use Ashio\OpenProvider\Model\Domain\DomainCheckDomainRequest;
use Ashio\OpenProvider\Model\Domain\DomainDomain;
use Ashio\OpenProvider\Model\Domain\TldTld;
use Ashio\OpenProvider\OpenProvider;
use Ashio\OpenProvider\Pagination\Listing;
use Ashio\OpenProvider\Pagination\Paginator;
use Ashio\OpenProvider\Response\ApiResponse;
use Ashio\OpenProvider\Serialization\Model;
use Ashio\OpenProvider\Smoke\Support\PacedHttpClient;
use Ashio\OpenProvider\Smoke\Support\ReadOnlyHttpClient;
use Ashio\OpenProvider\Support\SystemSleeper;
use GuzzleHttp\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The SDK against a real production account (https://api.openprovider.eu/v1), read-only. Run it
 * with `composer smoke:production` and OPENPROVIDER_PRODUCTION_USERNAME /
 * OPENPROVIDER_PRODUCTION_PASSWORD set; it is not part of `composer test`.
 *
 * Nothing it sends can change the account: ReadOnlyHttpClient refuses every request other than a
 * GET before it leaves the process, except the login and the domain availability check. It costs
 * nothing, so an account without balance can run it. Like the sandbox suite it fails without
 * credentials, and its calls are one second apart, at most 40 per run.
 *
 * A refused login is never repeated: the first one fails the test that made it, and every later
 * test fails without sending anything, because repeated failed logins get an account blocked.
 * Fields the typed models could not hold are listed at the end of the run, as a report on how
 * far the published specification is from what the API sends.
 */
final class ProductionSmokeTest extends TestCase
{
    private static ?OpenProvider $openProvider = null;
    private static ?PacedHttpClient $http = null;
    private static ?string $refusedLogin = null;

    /** @var array<string, true> Model.key of every field the typed properties could not hold */
    private static array $untyped = [];

    public function testTheSdkLogsInAndReadsTheReseller(): void
    {
        $reseller = self::read(static fn (OpenProvider $op): ApiResponse => $op->resellerCustomer()->getReseller())->data;

        self::assertIsInt($reseller->id);
        self::assertGreaterThan(0, $reseller->id);
    }

    /**
     * @return iterable<string, array{\Closure(OpenProvider): ApiResponse<Listing<Model>>}>
     */
    public static function listings(): iterable
    {
        yield 'domains' => [static fn (OpenProvider $op): ApiResponse => $op->domain()->listDomains(limit: 5)];
        yield 'TLDs' => [static fn (OpenProvider $op): ApiResponse => $op->domain()->listTlds(limit: 5)];
        yield 'customers' => [static fn (OpenProvider $op): ApiResponse => $op->resellerCustomer()->listCustomers(limit: 5)];
        yield 'contacts' => [static fn (OpenProvider $op): ApiResponse => $op->resellerCustomer()->listContacts(limit: 5)];
        yield 'DNS zones' => [static fn (OpenProvider $op): ApiResponse => $op->dns()->listZones(limit: 5)];
        yield 'nameserver groups' => [static fn (OpenProvider $op): ApiResponse => $op->dns()->listGroups(limit: 5)];
        yield 'SSL products' => [static fn (OpenProvider $op): ApiResponse => $op->ssl()->listProducts(limit: 5)];
        yield 'SSL orders' => [static fn (OpenProvider $op): ApiResponse => $op->ssl()->listOrders(limit: 5)];
    }

    /**
     * @param \Closure(OpenProvider): ApiResponse<Listing<Model>> $list
     */
    #[DataProvider('listings')]
    public function testEachListingArrivesAsATypedPage(\Closure $list): void
    {
        $page = self::read($list)->data;

        self::assertLessThanOrEqual(5, count($page->items()), 'the page respects the limit sent');
        $total = $page->total();
        if ($total !== null) {
            self::assertGreaterThanOrEqual(count($page->items()), $total);
        }
    }

    public function testATldReadsWithItsPrices(): void
    {
        $tld = self::read(static fn (OpenProvider $op): ApiResponse => $op->domain()->getTld('com', withPrice: true))->data;

        self::assertSame('com', $tld->name);
    }

    public function testADomainCheckAnswersForEachDomain(): void
    {
        $name = 'ashio-smoke-' . bin2hex(random_bytes(6));

        $results = self::read(static fn (OpenProvider $op): ApiResponse => $op->domain()->checkDomain(new DomainCheckDomainRequest(
            domains: [new DomainDomain(extension: 'com', name: $name)],
        )))->data->results ?? [];

        self::assertCount(1, $results);
        self::assertSame($name . '.com', $results[0]->domain);
        self::assertSame('free', $results[0]->status, 'a random name is free');
    }

    public function testThePaginatorWalksARealListing(): void
    {
        $tlds = self::read(static function (OpenProvider $op): array {
            $names = [];
            foreach (Paginator::all($op->domain()->listTlds(...), pageSize: 3, of: TldTld::class) as $tld) {
                self::collect($tld);
                $names[] = (string) $tld->name;
                if (count($names) === 6) {
                    break;
                }
            }

            return $names;
        });

        self::assertCount(6, $tlds, 'two pages of three');
        self::assertSame($tlds, array_values(array_unique($tlds)), 'the second page continues the first');
    }

    public function testWritesNeverLeaveTheProcess(): void
    {
        $sent = self::$http?->sent();

        try {
            self::client()->dns()->deleteZone('ashio-smoke-never-sent.com');
            self::fail('a write went through');
        } catch (\LogicException $e) {
            self::assertStringStartsWith('The production smoke suite only reads; DELETE ', $e->getMessage());
        }
        self::assertSame($sent, self::$http?->sent(), 'nothing was sent');
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$http === null) {
            return;
        }
        $fields = array_keys(self::$untyped);
        sort($fields);
        fwrite(STDERR, sprintf(
            "\nProduction smoke: %d requests sent; fields the models could not hold: %s\n",
            self::$http->sent(),
            $fields === [] ? 'none' : implode(', ', $fields),
        ));
    }

    /**
     * Makes one read, unless a login was refused before, and records its untyped fields.
     *
     * @template T
     *
     * @param \Closure(OpenProvider): T $call
     *
     * @return T
     */
    private static function read(\Closure $call): mixed
    {
        if (self::$refusedLogin !== null) {
            self::fail('Not sent: the login was refused earlier in this run (' . self::$refusedLogin . ')');
        }

        try {
            $result = $call(self::client());
        } catch (AuthenticationException $e) {
            self::$refusedLogin = sprintf('code %d: %s', $e->apiCode, $e->desc);

            self::fail('The login was refused (' . self::$refusedLogin . '). Check the username and password, that API access is enabled for this contact, and the IP allow-list; it is not retried.');
        }
        if ($result instanceof ApiResponse) {
            self::collect($result->data);
        }

        return $result;
    }

    /**
     * Records Model.key for each field of $value, at any depth, that only $extra could hold.
     */
    private static function collect(mixed $value, int $depth = 0): void
    {
        if ($depth > 8) {
            return;
        }
        if ($value instanceof Model) {
            $short = (new \ReflectionClass($value))->getShortName();
            foreach (get_object_vars($value) as $name => $field) {
                if ($name !== 'extra') {
                    self::collect($field, $depth + 1);
                } elseif (is_array($field)) {
                    foreach (array_keys($field) as $key) {
                        self::$untyped[$short . '.' . $key] = true;
                    }
                }
            }
        } elseif (is_array($value)) {
            foreach ($value as $item) {
                self::collect($item, $depth + 1);
            }
        }
    }

    private static function client(): OpenProvider
    {
        if (self::$openProvider === null) {
            $username = getenv('OPENPROVIDER_PRODUCTION_USERNAME');
            $password = getenv('OPENPROVIDER_PRODUCTION_PASSWORD');
            if (!is_string($username) || $username === '' || !is_string($password) || $password === '') {
                self::fail('Set OPENPROVIDER_PRODUCTION_USERNAME and OPENPROVIDER_PRODUCTION_PASSWORD to run the production smoke suite. It only reads.');
            }
            // The guard is outermost: a refused write is neither paced nor counted.
            self::$http = new PacedHttpClient(new Client(), new SystemSleeper(), static fn (): float => hrtime(true) / 1e9);
            self::$openProvider = new OpenProvider(ApiClient::create(ClientConfig::production(), new Credentials($username, $password), null, new ReadOnlyHttpClient(self::$http)));
        }

        return self::$openProvider;
    }
}
