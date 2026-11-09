<?php

namespace Ashio\Tests\Model;

use Ashio\Model\DnsRecord;
use Ashio\Model\DnsRecordUpdates;
use Ashio\Model\DnsTemplate;
use Ashio\Model\DnsTemplateRecord;
use PHPUnit\Framework\TestCase;

class MoreDnsModelsTest extends TestCase
{
    public function testDnsRecordConstructor(): void
    {
        $data = [
            'type' => 'A',
            'name' => 'www',
            'value' => '192.168.1.1',
            'ttl' => 3600,
        ];

        $record = new DnsRecord($data);

        $this->assertEquals('A', $record->type);
        $this->assertEquals('www', $record->name);
        $this->assertEquals('192.168.1.1', $record->value);
        $this->assertEquals(3600, $record->ttl);
    }

    public function testDnsRecordProperties(): void
    {
        $record = new DnsRecord();
        $record->type = 'MX';
        $record->name = '@';
        $record->value = 'mail.example.com';
        $record->prio = 10;

        $this->assertEquals('MX', $record->type);
        $this->assertEquals('@', $record->name);
        $this->assertEquals('mail.example.com', $record->value);
        $this->assertEquals(10, $record->prio);
    }

    public function testDnsTemplateFromArray(): void
    {
        $data = [
            'id' => 123,
            'name' => 'Default Template',
        ];

        $template = (new DnsTemplate())->fromArray($data);

        $this->assertEquals(123, $template->id);
        $this->assertEquals('Default Template', $template->name);
    }

    public function testDnsTemplateRecordFromArray(): void
    {
        $data = [
            'type' => 'CNAME',
            'name' => 'www',
            'value' => '@',
        ];

        $record = (new DnsTemplateRecord())->fromArray($data);

        $this->assertEquals('CNAME', $record->type);
        $this->assertEquals('www', $record->name);
        $this->assertEquals('@', $record->value);
    }

    public function testDnsRecordUpdatesToArray(): void
    {
        $updates = new DnsRecordUpdates();
        $record1 = new DnsRecord(['type' => 'A', 'name' => 'test', 'value' => '1.2.3.4']);
        $record2 = new DnsRecord(['type' => 'CNAME', 'name' => 'www', 'value' => '@']);

        $updates->add = [$record1];
        $updates->remove = [$record2];

        $array = $updates->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('add', $array);
        $this->assertArrayHasKey('remove', $array);
        $this->assertCount(1, $array['add']);
        $this->assertCount(1, $array['remove']);
    }

    public function testEmptyDnsRecord(): void
    {
        $record = new DnsRecord();

        $this->assertNull($record->type);
        $this->assertNull($record->name);
        $this->assertNull($record->value);
    }

    public function testEmptyDnsTemplate(): void
    {
        $template = new DnsTemplate();

        $this->assertNull($template->id);
        $this->assertNull($template->name);
    }

    public function testDnsRecordWithAllFields(): void
    {
        $data = [
            'type' => 'TXT',
            'name' => '_dmarc',
            'value' => 'v=DMARC1; p=none',
            'ttl' => 86400,
            'prio' => 0,
        ];

        $record = new DnsRecord($data);

        $this->assertEquals('TXT', $record->type);
        $this->assertEquals('_dmarc', $record->name);
        $this->assertEquals('v=DMARC1; p=none', $record->value);
        $this->assertEquals(86400, $record->ttl);
        $this->assertEquals(0, $record->prio);
    }
}
