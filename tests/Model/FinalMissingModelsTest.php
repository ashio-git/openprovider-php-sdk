<?php

namespace Ashio\Tests\Model;

use Ashio\Model\DnsActionResponse;
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
        $response = new DnsActionResponse();
        $response->success = true;
        $response->message = 'Zone updated';

        $this->assertTrue($response->success);
        $this->assertEquals('Zone updated', $response->message);
    }

    public function testDomainSendFoa1RequestConstructor(): void
    {
        $request = new DomainSendFoa1Request();
        $domainName = new \Ashio\Model\DomainName('example', 'com');
        $request->domain = $domainName;

        $this->assertInstanceOf(\Ashio\Model\DomainName::class, $request->domain);
    }

    public function testDomainSuggestNameRequestConstructor(): void
    {
        $request = new DomainSuggestNameRequest();
        $request->name = 'example';
        $request->limit = 10;

        $this->assertEquals('example', $request->name);
        $this->assertEquals(10, $request->limit);
    }

    public function testDomainWhoisPrivacyDataConstructor(): void
    {
        $data = new DomainWhoisPrivacyData();
        $data->enabled = true;

        $this->assertTrue($data->enabled);
    }

    public function testEasyDmarcDomainConstructor(): void
    {
        $domain = new EasyDmarcDomain();
        $domain->name = 'example.com';
        $domain->status = 'active';

        $this->assertEquals('example.com', $domain->name);
        $this->assertEquals('active', $domain->status);
    }

    public function testEasyDmarcRecentActionConstructor(): void
    {
        $action = new EasyDmarcRecentAction();
        $action->action = 'created';
        $action->timestamp = '2024-01-01';

        $this->assertEquals('created', $action->action);
        $this->assertEquals('2024-01-01', $action->timestamp);
    }

    public function testSpamExpertAliasUpdatesConstructor(): void
    {
        $updates = new SpamExpertAliasUpdates();
        $updates->add = ['alias1@example.com'];
        $updates->remove = ['alias2@example.com'];

        $this->assertIsArray($updates->add);
        $this->assertIsArray($updates->remove);
    }

    public function testSpamExpertDestinationConstructor(): void
    {
        $destination = new SpamExpertDestination();
        $destination->destination = 'mail.example.com';
        $destination->port = 25;

        $this->assertEquals('mail.example.com', $destination->destination);
        $this->assertEquals(25, $destination->port);
    }

    public function testSpamExpertProductsConstructor(): void
    {
        $products = new SpamExpertProducts();
        $products->incoming = true;
        $products->outgoing = false;

        $this->assertTrue($products->incoming);
        $this->assertFalse($products->outgoing);
    }

    public function testSpamExpertRecordsConstructor(): void
    {
        $records = new SpamExpertRecords();
        $records->mx = ['mx1.spamexperts.com'];

        $this->assertIsArray($records->mx);
    }

    public function testEmptyDnsActionResponse(): void
    {
        $response = new DnsActionResponse();

        $this->assertNull($response->success);
        $this->assertNull($response->message);
    }
}
