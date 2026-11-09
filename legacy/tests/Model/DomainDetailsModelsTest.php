<?php

namespace Ashio\Tests\Model;

use Ashio\Model\DomainAbuseDetails;
use Ashio\Model\DomainDnssecKey;
use Ashio\Model\DomainOwner;
use Ashio\Model\DomainRegistryStatus;
use Ashio\Model\DomainRegistryStatuses;
use PHPUnit\Framework\TestCase;

class DomainDetailsModelsTest extends TestCase
{
    public function testDomainAbuseDetailsFromArray(): void
    {
        $data = [
            'abuse_id' => 12345,
            'is_domain_held' => true,
            'message' => 'Domain held for abuse',
        ];

        $details = (new DomainAbuseDetails())->fromArray($data);

        $this->assertEquals(12345, $details->abuse_id);
        $this->assertTrue($details->is_domain_held);
        $this->assertEquals('Domain held for abuse', $details->message);
    }

    public function testDomainDnssecKeyFromArray(): void
    {
        $data = [
            'flags' => 257,
            'alg' => 8,
            'protocol' => 3,
            'pub_key' => 'AQPJ....',
        ];

        $key = (new DomainDnssecKey())->fromArray($data);

        $this->assertEquals(257, $key->flags);
        $this->assertEquals(8, $key->alg);
        $this->assertEquals(3, $key->protocol);
    }

    public function testDomainOwnerFromArray(): void
    {
        $data = [
            'company_name' => 'Example Inc',
            'full_name' => 'John Doe',
        ];

        $owner = (new DomainOwner())->fromArray($data);

        $this->assertEquals('Example Inc', $owner->company_name);
        $this->assertEquals('John Doe', $owner->full_name);
    }

    public function testDomainRegistryStatusFromArray(): void
    {
        $data = [
            'is_changeable' => false,
            'value' => 1,
        ];

        $status = (new DomainRegistryStatus())->fromArray($data);

        $this->assertFalse($status->is_changeable);
        $this->assertEquals(1, $status->value);
    }

    public function testDomainRegistryStatusesFromArray(): void
    {
        $data = [
            'ok' => ['status' => 'ok'],
            'clientTransferProhibited' => ['status' => 'clientTransferProhibited'],
        ];

        $statuses = new DomainRegistryStatuses($data);

        $this->assertIsArray($statuses->statuses);
        $this->assertCount(2, $statuses->statuses);
    }

    public function testEmptyDomainAbuseDetails(): void
    {
        $details = new DomainAbuseDetails();

        $this->assertNull($details->abuse_id);
        $this->assertNull($details->is_domain_held);
        $this->assertNull($details->message);
    }

    public function testEmptyDomainDnssecKey(): void
    {
        $key = new DomainDnssecKey();

        $this->assertNull($key->flags);
        $this->assertNull($key->alg);
    }

    public function testEmptyDomainOwner(): void
    {
        $owner = new DomainOwner();

        $this->assertNull($owner->company_name);
        $this->assertNull($owner->full_name);
    }
}
