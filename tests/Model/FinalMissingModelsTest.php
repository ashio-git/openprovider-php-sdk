<?php

namespace Ashio\Tests\Model;

use Ashio\Model\DnsActionResponse;
use Ashio\Model\DomainName;
use Ashio\Model\DomainSendFoa1Request;
use Ashio\Model\DomainSuggestNameRequest;
use Ashio\Model\DomainWhoisPrivacyData;
use Ashio\Model\EasyDmarcDomain;
use Ashio\Model\EasyDmarcRecentAction;
use Ashio\Model\SpamExpertAliasUpdates;
use Ashio\Model\SpamExpertDestination;
use Ashio\Model\SpamExpertProducts;
use Ashio\Model\SpamExpertRecords;
use PHPUnit\Framework\TestCase;

class FinalMissingModelsTest extends TestCase
{
    public function testDnsActionResponseConstructor(): void
    {
        $response = (new DnsActionResponse())->fromArray(['success' => true]);

        $this->assertTrue($response->success);
        $this->assertFalse((new DnsActionResponse())->fromArray(['success' => false])->success);
    }

    public function testDomainSendFoa1RequestConstructor(): void
    {
        $request = new DomainSendFoa1Request();
        $request->domain = (new DomainName())->fromArray(['name' => 'example', 'extension' => 'com']);

        $this->assertInstanceOf(DomainName::class, $request->domain);
        $this->assertSame('example', $request->domain->name);
        $this->assertSame('com', $request->domain->extension);
        $this->assertSame(['domain' => ['name' => 'example', 'extension' => 'com']], $request->toArray());
        $this->assertSame([], (new DomainSendFoa1Request())->toArray());
    }

    public function testDomainSuggestNameRequestConstructor(): void
    {
        $request = new DomainSuggestNameRequest();
        $request->name = 'example';
        $request->limit = 10;
        $request->language = 'eng';
        $request->sensitive = false;
        $request->tlds = ['com', 'net'];

        $this->assertSame([
            'language' => 'eng',
            'limit' => 10,
            'name' => 'example',
            'sensitive' => false,
            'tlds' => ['com', 'net'],
        ], $request->toArray());
    }

    public function testDomainWhoisPrivacyDataConstructor(): void
    {
        $data = new DomainWhoisPrivacyData(['expiration_date' => '2025-06-30 00:00:00']);

        $this->assertSame('2025-06-30 00:00:00', $data->expiration_date);
        $this->assertNull((new DomainWhoisPrivacyData())->expiration_date);
        $this->assertSame(
            '2026-01-01 00:00:00',
            (new DomainWhoisPrivacyData())->fromArray(['expiration_date' => '2026-01-01 00:00:00'])->expiration_date
        );
    }

    public function testEasyDmarcDomainConstructor(): void
    {
        $domain = (new EasyDmarcDomain())->fromArray(['name' => 'example', 'extension' => 'com']);

        $this->assertSame('example', $domain->name);
        $this->assertSame('com', $domain->extension);
    }

    public function testEasyDmarcRecentActionConstructor(): void
    {
        $data = ['recent_action' => 'create', 'message' => 'DMARC record created'];

        $action = (new EasyDmarcRecentAction())->fromArray($data);

        $this->assertSame('create', $action->recent_action);
        $this->assertSame('DMARC record created', $action->message);
        $this->assertSame($data, $action->toArray());
    }

    public function testSpamExpertAliasUpdatesConstructor(): void
    {
        $updates = new SpamExpertAliasUpdates();
        $updates->add = ['alias1.example.com'];
        $updates->remove = ['alias2.example.com'];

        $this->assertSame([
            'add' => ['alias1.example.com'],
            'remove' => ['alias2.example.com'],
        ], $updates->toArray());

        $addOnly = new SpamExpertAliasUpdates();
        $addOnly->add = ['alias3.example.com'];
        $this->assertSame(['add' => ['alias3.example.com']], $addOnly->toArray());
    }

    public function testSpamExpertDestinationConstructor(): void
    {
        $data = ['hostname' => 'mail.example.com', 'port' => 25];

        $destination = (new SpamExpertDestination())->fromArray($data);

        $this->assertSame('mail.example.com', $destination->hostname);
        $this->assertSame(25, $destination->port);
        $this->assertSame($data, $destination->toArray());
    }

    public function testSpamExpertProductsConstructor(): void
    {
        $data = ['incoming' => true, 'outgoing' => false, 'archiving' => true];

        $products = (new SpamExpertProducts())->fromArray($data);

        $this->assertTrue($products->incoming);
        $this->assertFalse($products->outgoing);
        $this->assertTrue($products->archiving);
        // false is a real value and must survive the null filter.
        $this->assertSame($data, $products->toArray());
    }

    public function testSpamExpertRecordsConstructor(): void
    {
        $records = (new SpamExpertRecords())->fromArray([
            'aliases' => ['alias.example.com'],
            'destinations' => [
                ['hostname' => 'mx1.example.com', 'port' => 25],
                ['hostname' => 'mx2.example.com', 'port' => 587],
            ],
            'outgoing_user_ips' => ['192.0.2.25'],
        ]);

        $this->assertSame(['alias.example.com'], $records->aliases);
        $this->assertSame(['192.0.2.25'], $records->outgoing_user_ips);
        $this->assertCount(2, $records->destinations);
        $this->assertInstanceOf(SpamExpertDestination::class, $records->destinations[1]);
        $this->assertSame('mx2.example.com', $records->destinations[1]->hostname);
        $this->assertSame(587, $records->destinations[1]->port);
    }

    public function testEmptyDnsActionResponse(): void
    {
        $response = new DnsActionResponse();

        $this->assertNull($response->success);
        $this->assertNull((new DnsActionResponse())->fromArray([])->success);
    }
}
