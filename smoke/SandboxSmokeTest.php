<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Smoke;

use Ashio\OpenProvider\Auth\Credentials;
use Ashio\OpenProvider\Client\ApiClient;
use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Model\Dns\RecordRecordInfo;
use Ashio\OpenProvider\Model\Dns\ZoneCreateZoneRequest;
use Ashio\OpenProvider\Model\Dns\ZoneDomain;
use Ashio\OpenProvider\Model\Dns\ZoneRecord;
use Ashio\OpenProvider\Model\Dns\ZoneRecordUpdates;
use Ashio\OpenProvider\Model\Dns\ZoneUpdateZoneRequest;
use Ashio\OpenProvider\Model\Domain\DomainCheckDomainRequest;
use Ashio\OpenProvider\Model\Domain\DomainDomain;
use Ashio\OpenProvider\OpenProvider;
use Ashio\OpenProvider\Pagination\Paginator;
use Ashio\OpenProvider\Smoke\Support\PacedHttpClient;
use Ashio\OpenProvider\Support\SystemSleeper;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * S7: the SDK against the real sandbox (https://api.sandbox.openprovider.nl/v1). Run it with
 * `composer smoke` and OPENPROVIDER_SANDBOX_USERNAME / OPENPROVIDER_SANDBOX_PASSWORD set; it is
 * not part of `composer test`, which never reaches the network.
 *
 * Without credentials every test fails: a smoke run that skips would read as green. All calls
 * share one paced transport (one second apart, at most 40 per run), each call is made once, and
 * the DNS zone the lifecycle creates is deleted even when an assertion fails.
 */
final class SandboxSmokeTest extends TestCase
{
    private static ?OpenProvider $openProvider = null;
    private static ?PacedHttpClient $http = null;

    public function testTheSdkLogsInAndReadsTheReseller(): void
    {
        // The login is the first call: its success also shows the sandbox accepts the SDK's
        // User-Agent (spec/behavior.json `userAgent`).
        $reseller = self::openProvider()->resellerCustomer()->getReseller()->data;

        self::assertIsInt($reseller->id);
        self::assertGreaterThan(0, $reseller->id);
    }

    public function testListingsArriveAsModelsThroughThePaginator(): void
    {
        $tlds = [];
        foreach (Paginator::all(self::openProvider()->domain()->listTlds(...), pageSize: 5) as $tld) {
            $tlds[] = $tld;
            if (count($tlds) === 5) {
                break;
            }
        }

        self::assertCount(5, $tlds, 'the sandbox lists at least five TLDs');
    }

    public function testADomainCheckAnswersForEachDomain(): void
    {
        $name = 'ashio-smoke-' . bin2hex(random_bytes(6));

        $results = self::openProvider()->domain()->checkDomain(new DomainCheckDomainRequest(
            domains: [new DomainDomain(extension: 'com', name: $name)],
        ))->data->results ?? [];

        self::assertCount(1, $results);
        self::assertSame($name . '.com', $results[0]->domain);
        self::assertNotSame('', (string) $results[0]->status);
    }

    public function testADnsZoneGoesThroughItsLifecycle(): void
    {
        $name = 'ashio-smoke-' . bin2hex(random_bytes(6));
        $zone = $name . '.com';
        $dns = self::openProvider()->dns();
        $created = false;

        try {
            $dns->createZone(new ZoneCreateZoneRequest(
                domain: new ZoneDomain(extension: 'com', name: $name),
                records: [new ZoneRecord(name: '', ttl: 900, type: 'A', value: '192.0.2.10')],
                type: 'master',
            ));
            $created = true;

            self::assertSame($zone, $dns->getZone($zone)->data->name);

            $dns->updateZone($zone, new ZoneUpdateZoneRequest(
                domain: new ZoneDomain(extension: 'com', name: $name),
                records: new ZoneRecordUpdates(add: [new ZoneRecord(name: '', ttl: 900, type: 'TXT', value: '"ashio-smoke"')]),
            ));

            $types = array_map(
                static fn (RecordRecordInfo $record): ?string => $record->type,
                $dns->listZoneRecords($zone)->data->results ?? [],
            );
            self::assertContains('A', $types);
            self::assertContains('TXT', $types);
        } finally {
            if ($created) {
                $dns->deleteZone($zone);
            }
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$http !== null) {
            fwrite(STDERR, sprintf("\nSandbox smoke: %d requests sent\n", self::$http->sent()));
        }
    }

    private static function openProvider(): OpenProvider
    {
        if (self::$openProvider === null) {
            $username = getenv('OPENPROVIDER_SANDBOX_USERNAME');
            $password = getenv('OPENPROVIDER_SANDBOX_PASSWORD');
            if (!is_string($username) || $username === '' || !is_string($password) || $password === '') {
                self::fail('Set OPENPROVIDER_SANDBOX_USERNAME and OPENPROVIDER_SANDBOX_PASSWORD to run the sandbox smoke suite (plan S7, decision D5).');
            }
            self::$http = new PacedHttpClient(new Client(), new SystemSleeper(), static fn (): float => hrtime(true) / 1e9);
            self::$openProvider = new OpenProvider(ApiClient::create(ClientConfig::sandbox(), new Credentials($username, $password), null, self::$http));
        }

        return self::$openProvider;
    }
}
