<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Service;

use Ashio\OpenProvider\Auth\Credentials;
use Ashio\OpenProvider\Client\ApiClient;
use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Model\Dns\DomainTokenCreateTokenRequest;
use Ashio\OpenProvider\Model\Dns\DomainTokenCreateTokenResponseData;
use Ashio\OpenProvider\Model\Domain\DomainCreateDomainRequest;
use Ashio\OpenProvider\Model\Domain\DomainUpdateDomainRequest;
use Ashio\OpenProvider\Model\Domain\PriceGetPriceResponseData;
use Ashio\OpenProvider\Model\EasyDmarc\OrderDeleteEasyDmarcResponseData;
use Ashio\OpenProvider\Model\ResellerCustomer\ResellerReseller;
use Ashio\OpenProvider\Model\ResellerCustomer\ResellerSettings;
use Ashio\OpenProvider\Model\ResellerCustomer\ResellerStatistics;
use Ashio\OpenProvider\Model\ResellerCustomer\SettingsGetSettingsResponseData;
use Ashio\OpenProvider\Model\ResellerCustomer\StatisticsGetStatisticsResponseData;
use Ashio\OpenProvider\Model\Ssl\OtptokenCreateOtpTokenRequest;
use Ashio\OpenProvider\Model\Ssl\OtptokenCreateOtpTokenResponseData;
use Ashio\OpenProvider\OpenProvider;
use Ashio\OpenProvider\Tests\Support\ArrayPath;
use Ashio\OpenProvider\Tests\Support\FakeHttpClient;
use Ashio\OpenProvider\Tests\Support\SpecOperations;
use PHPUnit\Framework\TestCase;

/**
 * The 15 MAJOR findings of the conformance study (docs/study/2026-11-09/conformance.md, section
 * 3), each pinned as the literal request the 2.x SDK got wrong or the response field it lost.
 */
final class MajorDivergenceRegressionTest extends TestCase
{
    private FakeHttpClient $http;
    private OpenProvider $openProvider;

    protected function setUp(): void
    {
        $this->http = (new FakeHttpClient())->login();
        $this->openProvider = new OpenProvider(ApiClient::create(ClientConfig::sandbox(), new Credentials('reseller', 'pw'), null, $this->http));
    }

    public function testListCustomersSendsTheSnakeCaseFiltersAndRepeatsColumns(): void
    {
        $this->reply(['results' => [], 'total' => 0]);

        $this->openProvider->resellerCustomer()->listCustomers(
            resellerId: 5,
            orderBy: 'name',
            emailPattern: 'a*',
            companyNamePattern: 'b*',
            lastNamePattern: 'c*',
            firstNamePattern: 'd*',
            commentPattern: 'e*',
            handlePattern: 'f*',
            withAdditionalData: false,
            columns: ['handle', 'email'],
        );

        self::assertSame(
            'GET /v1/customers?reseller_id=5&order_by=name&email_pattern=a%2A&company_name_pattern=b%2A&last_name_pattern=c%2A'
            . '&first_name_pattern=d%2A&comment_pattern=e%2A&handle_pattern=f%2A&with_additional_data=false&columns=handle&columns=email',
            $this->sent(),
        );
    }

    public function testGetPriceSaysWhichDomainToPriceAndReadsOnePrice(): void
    {
        $this->reply(['is_premium' => false, 'price' => ['product' => ['price' => 9.5, 'currency' => 'EUR']]]);

        $price = $this->openProvider->domain()->getPrice(domainName: 'example', domainExtension: 'com', operation: 'create', period: 2)->data;

        self::assertSame('GET /v1/domains/prices?domain.name=example&domain.extension=com&operation=create&period=2', $this->sent());
        self::assertInstanceOf(PriceGetPriceResponseData::class, $price);
        self::assertFalse($price->isPremium);
        self::assertSame(9.5, $price->price?->product?->price);
    }

    public function testCreateDomainCanSendEveryFieldOfTheSpec(): void
    {
        $this->assertSendsEveryField('domain.domainCreateDomainRequest', 31, function (array $payload): void {
            $this->reply(['id' => 1, 'status' => 'ACT']);
            $this->openProvider->domain()->createDomain(DomainCreateDomainRequest::fromArray($payload));
            self::assertSame('POST /v1/domains', $this->sent());
        });
    }

    public function testUpdateDomainCanSendEveryFieldOfTheSpec(): void
    {
        $this->assertSendsEveryField('domain.domainUpdateDomainRequest', 27, function (array $payload): void {
            $this->reply(['id' => 7, 'status' => 'ACT']);
            $this->openProvider->domain()->updateDomain(7, DomainUpdateDomainRequest::fromArray($payload));
            self::assertSame('PUT /v1/domains/7', $this->sent());
        });
    }

    public function testDeleteDomainSendsItsFiltersInTheQuery(): void
    {
        $this->reply(['status' => 'DEL']);

        $this->openProvider->domain()->deleteDomain(7, domainName: 'example', domainExtension: 'com', type: 'deregister', skipSoftQuarantine: false, forceDelete: true);

        self::assertSame('DELETE /v1/domains/7?domain.name=example&domain.extension=com&type=deregister&skip_soft_quarantine=false&force_delete=true', $this->sent());
        self::assertSame('', $this->body());
    }

    public function testCreateTokenSendsTheDomainAsAStringAndReadsTheUrl(): void
    {
        $this->reply(['token' => 'abc', 'url' => 'https://dns.example/abc']);

        $token = $this->openProvider->dns()->createToken(new DomainTokenCreateTokenRequest(domain: 'example.com', zoneProvider: 'openprovider'))->data;

        self::assertSame('POST /v1/dns/domain-token', $this->sent());
        self::assertSame('{"domain":"example.com","zone_provider":"openprovider"}', $this->body());
        self::assertInstanceOf(DomainTokenCreateTokenResponseData::class, $token);
        self::assertSame('https://dns.example/abc', $token->url);
    }

    public function testDeleteEasyDmarcReadsTheStatusTheSpecReturns(): void
    {
        $this->reply(['status' => 'deleted']);

        $result = $this->openProvider->easyDmarc()->deleteEasyDmarc(12)->data;

        self::assertSame('DELETE /v1/easydmarcs/12', $this->sent());
        self::assertInstanceOf(OrderDeleteEasyDmarcResponseData::class, $result);
        self::assertSame('deleted', $result->status);
    }

    public function testListDomainEmailVerificationsSendsTheDottedResponseFilters(): void
    {
        $this->reply(['results' => [], 'total' => 0]);

        $this->openProvider->resellerCustomer()->listDomainEmailVerifications(responseType: 'json', responseTo: 'api');

        self::assertSame('GET /v1/customers/verifications/emails/domains?response.type=json&response.to=api', $this->sent());
    }

    public function testCreateOtpTokenReadsTheToken(): void
    {
        $this->reply(['token' => 'otp-1', 'uri' => 'otpauth://x', 'expire_at' => '2026-11-10 12:00:00']);

        $otp = $this->openProvider->ssl()->createOtpToken(3, new OtptokenCreateOtpTokenRequest(id: 3))->data;

        self::assertSame('POST /v1/ssl/orders/3/otp-tokens', $this->sent());
        self::assertInstanceOf(OtptokenCreateOtpTokenResponseData::class, $otp);
        self::assertSame(['otp-1', 'otpauth://x', '2026-11-10 12:00:00'], [$otp->token, $otp->uri, $otp->expireAt]);
    }

    public function testGetResellerSendsTheWithFlagsAndReadsBalanceSettingsAndStatistics(): void
    {
        $this->reply([
            'id' => 7,
            'balance' => 120.5,
            'reserved_balance' => 0,
            'settings' => ['av_handle' => 'AV1', 'currency' => 'EUR'],
            'statistics' => ['currency' => 'EUR'],
        ]);

        $reseller = $this->openProvider->resellerCustomer()->getReseller(withAdditionalData: false, withStatistics: true, withSettings: true, withContacts: false)->data;

        self::assertSame('GET /v1/resellers?with_additional_data=false&with_statistics=true&with_settings=true&with_contacts=false', $this->sent());
        self::assertInstanceOf(ResellerReseller::class, $reseller);
        self::assertSame([120.5, 0], [$reseller->balance, $reseller->reservedBalance]);
        self::assertInstanceOf(ResellerSettings::class, $reseller->settings);
        self::assertSame('AV1', $reseller->settings->avHandle);
        self::assertInstanceOf(ResellerStatistics::class, $reseller->statistics);
        self::assertSame([], $reseller->extra);
    }

    public function testSpamExpertsDeleteDomainSendsBundleInTheQuery(): void
    {
        $this->reply(['success' => true]);

        $this->openProvider->spamExperts()->deleteDomain('example.com', bundle: true);

        self::assertSame('DELETE /v1/spam-expert/domains/example.com?bundle=true', $this->sent());
        self::assertSame('', $this->body());
    }

    public function testGetSettingsReadsEveryFieldOfTheSpec(): void
    {
        $this->assertReadsEveryField('reseller-customer.settingsGetSettingsResponseData', 41, function (): object {
            return $this->openProvider->resellerCustomer()->getSettings()->data;
        }, SettingsGetSettingsResponseData::class);
        self::assertSame('GET /v1/resellers/settings', $this->sent());
    }

    public function testGetStatisticsReadsEveryFieldOfTheSpec(): void
    {
        $this->assertReadsEveryField('reseller-customer.statisticsGetStatisticsResponseData', 8, function (): object {
            return $this->openProvider->resellerCustomer()->getStatistics()->data;
        }, StatisticsGetStatisticsResponseData::class);
        self::assertSame('GET /v1/resellers/statistics', $this->sent());
    }

    public function testDeleteTagSendsTheSelectorInTheQuery(): void
    {
        $this->reply(['success' => true]);

        $this->openProvider->resellerCustomer()->deleteTag(key: 'env', value: 'prod');

        self::assertSame('DELETE /v1/tags?key=env&value=prod', $this->sent());
        self::assertSame('', $this->body());
    }

    public function testDeleteZoneSendsItsFiltersInTheQuery(): void
    {
        $this->reply(['success' => true]);

        $this->openProvider->dns()->deleteZone('example.com', id: 5, domainName: 'example', domainExtension: 'com', provider: 'openprovider');

        self::assertSame('DELETE /v1/dns/zones/example.com?id=5&domain.name=example&domain.extension=com&provider=openprovider', $this->sent());
        self::assertSame('', $this->body());
    }

    /**
     * @param \Closure(array<array-key, mixed>): void $send
     */
    private function assertSendsEveryField(string $definition, int $fields, \Closure $send): void
    {
        $properties = ArrayPath::at(SpecOperations::definitions(), $definition, 'properties');
        self::assertIsArray($properties);
        self::assertCount($fields, $properties);
        $payload = [];
        foreach (array_keys($properties) as $i => $key) {
            $payload[(string) $key] = 'v' . $i;
        }

        $send($payload);

        $sent = json_decode($this->body(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($sent);
        self::assertEqualsCanonicalizing(array_keys($properties), array_keys($sent), "every field of $definition is sent");
    }

    /**
     * @param \Closure(): object $read
     * @param class-string       $class
     */
    private function assertReadsEveryField(string $definition, int $fields, \Closure $read, string $class): void
    {
        $properties = ArrayPath::at(SpecOperations::definitions(), $definition, 'properties');
        self::assertIsArray($properties);
        self::assertCount($fields, $properties);
        $this->reply(array_fill_keys(array_map('strval', array_keys($properties)), null));
        $model = $read();
        self::assertInstanceOf($class, $model);

        $declared = array_map(
            static fn (\ReflectionProperty $p): string => $p->getName(),
            array_filter((new \ReflectionClass($class))->getProperties(), static fn (\ReflectionProperty $p): bool => $p->getName() !== 'extra'),
        );
        self::assertEqualsCanonicalizing(array_map(SpecOperations::camel(...), array_map('strval', array_keys($properties))), $declared, "$class models every field of $definition");
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function reply(array $data): void
    {
        $this->http->reply(['code' => 0, 'desc' => '', 'data' => $data]);
    }

    private function sent(): string
    {
        $targets = $this->http->targets();

        return $targets[count($targets) - 1];
    }

    private function body(): string
    {
        return (string) $this->http->requests[count($this->http->requests) - 1]->getBody();
    }
}
