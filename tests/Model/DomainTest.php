<?php

namespace Ashio\Tests\Model;

use Ashio\Model\Domain;
use Ashio\Model\DomainAbuseDetails;
use Ashio\Model\DomainDnssecKey;
use Ashio\Model\DomainName;
use Ashio\Model\DomainOwner;
use Ashio\Model\Nameserver;
use PHPUnit\Framework\TestCase;

class DomainTest extends TestCase
{
    public function testConstructorWithEmptyArray()
    {
        $domain = new Domain([]);

        $this->assertInstanceOf(Domain::class, $domain);
        $this->assertNull($domain->id);
    }

    public function testConstructorWithCompleteData()
    {
        $data = [
            'id' => 123,
            'domain' => ['name' => 'example', 'extension' => 'com'],
            'owner' => ['company_name' => 'Test Company', 'full_name' => 'John Doe'],
            'name_servers' => [
                ['name' => 'ns1.example.com', 'ip' => '1.2.3.4'],
                ['name' => 'ns2.example.com', 'ip' => '5.6.7.8'],
            ],
            'dnssec_keys' => [
                ['alg' => 8, 'flags' => 257, 'protocol' => 3, 'pub_key' => 'abc123'],
            ],
            'abuse_details' => ['abuse_id' => 1, 'is_domain_held' => false],
        ];

        $domain = new Domain($data);

        $this->assertEquals(123, $domain->id);
        $this->assertInstanceOf(DomainName::class, $domain->domain);
        $this->assertEquals('example', $domain->domain->name);
        $this->assertInstanceOf(DomainOwner::class, $domain->owner);
        $this->assertIsArray($domain->name_servers);
        $this->assertCount(2, $domain->name_servers);
        $this->assertInstanceOf(Nameserver::class, $domain->name_servers[0]);
        $this->assertIsArray($domain->dnssec_keys);
        $this->assertInstanceOf(DomainDnssecKey::class, $domain->dnssec_keys[0]);
        $this->assertInstanceOf(DomainAbuseDetails::class, $domain->abuse_details);
    }

    public function testFromArrayWithMinimalData()
    {
        $data = ['id' => 456];

        $domain = (new Domain())->fromArray($data);

        $this->assertEquals(456, $domain->id);
        $this->assertNull($domain->domain);
        $this->assertNull($domain->owner);
    }

    public function testFromArrayWithNestedObjects()
    {
        $data = [
            'id' => 789,
            'domain' => ['name' => 'test', 'extension' => 'org'],
            'owner' => ['full_name' => 'Jane Smith'],
        ];

        $domain = (new Domain())->fromArray($data);

        $this->assertInstanceOf(DomainName::class, $domain->domain);
        $this->assertEquals('test', $domain->domain->name);
        $this->assertInstanceOf(DomainOwner::class, $domain->owner);
    }

    public function testFromArrayWithNameservers()
    {
        $data = [
            'id' => 100,
            'name_servers' => [
                ['name' => 'ns1.test.com'],
                ['name' => 'ns2.test.com'],
                ['name' => 'ns3.test.com'],
            ],
        ];

        $domain = (new Domain())->fromArray($data);

        $this->assertIsArray($domain->name_servers);
        $this->assertCount(3, $domain->name_servers);
        foreach ($domain->name_servers as $ns) {
            $this->assertInstanceOf(Nameserver::class, $ns);
        }
    }

    public function testFromArrayWithDnssecKeys()
    {
        $data = [
            'id' => 200,
            'dnssec_keys' => [
                ['alg' => 8, 'flags' => 257],
                ['alg' => 13, 'flags' => 256],
            ],
        ];

        $domain = (new Domain())->fromArray($data);

        $this->assertIsArray($domain->dnssec_keys);
        $this->assertCount(2, $domain->dnssec_keys);
        foreach ($domain->dnssec_keys as $key) {
            $this->assertInstanceOf(DomainDnssecKey::class, $key);
        }
    }

    public function testFromArrayChaining()
    {
        $domain = (new Domain())->fromArray(['id' => 999]);

        $this->assertInstanceOf(Domain::class, $domain);
        $this->assertEquals(999, $domain->id);
    }

    public function testEmptyDomain()
    {
        $domain = new Domain();

        $this->assertNull($domain->id);
        $this->assertNull($domain->domain);
        $this->assertNull($domain->owner);
    }

    public function testDomainWithAbuseDetails()
    {
        $data = [
            'id' => 300,
            'abuse_details' => [
                'abuse_id' => 5,
                'is_domain_held' => true,
                'message' => 'Abuse detected',
            ],
        ];

        $domain = (new Domain())->fromArray($data);

        $this->assertInstanceOf(DomainAbuseDetails::class, $domain->abuse_details);
        $this->assertEquals(5, $domain->abuse_details->abuse_id);
    }

    public function testDomainWithAllFields()
    {
        $data = [
            'id' => 400,
            'domain' => ['name' => 'complete', 'extension' => 'net'],
            'owner' => ['company_name' => 'Complete Inc'],
            'name_servers' => [['name' => 'ns.complete.net']],
            'dnssec_keys' => [['alg' => 8]],
            'abuse_details' => ['abuse_id' => 10],
            'status' => 'active',
            'autorenew' => 'on',
        ];

        $domain = (new Domain())->fromArray($data);

        $this->assertEquals(400, $domain->id);
        $this->assertInstanceOf(DomainName::class, $domain->domain);
        $this->assertInstanceOf(DomainOwner::class, $domain->owner);
        $this->assertIsArray($domain->name_servers);
        $this->assertIsArray($domain->dnssec_keys);
        $this->assertInstanceOf(DomainAbuseDetails::class, $domain->abuse_details);
    }
}
