<?php

namespace Ashio\Tests\Model;

use Ashio\Model\Nameserver;
use PHPUnit\Framework\TestCase;

class NameserverTest extends TestCase
{
    public function testFromArrayWithCompleteData()
    {
        $data = [
            'name' => 'ns1.example.com',
            'ip' => '192.168.1.1',
            'ip6' => '2001:db8::1',
        ];

        $nameserver = (new Nameserver())->fromArray($data);

        $this->assertEquals('ns1.example.com', $nameserver->name);
        $this->assertEquals('192.168.1.1', $nameserver->ip);
        $this->assertEquals('2001:db8::1', $nameserver->ip6);
    }

    public function testFromArrayWithMinimalData()
    {
        $data = ['name' => 'ns2.example.com'];

        $nameserver = (new Nameserver())->fromArray($data);

        $this->assertEquals('ns2.example.com', $nameserver->name);
        $this->assertNull($nameserver->ip);
        $this->assertNull($nameserver->ip6);
    }

    public function testToArrayWithCompleteData()
    {
        $nameserver = new Nameserver();
        $nameserver->name = 'ns3.example.com';
        $nameserver->ip = '10.0.0.1';
        $nameserver->ip6 = 'fe80::1';

        $array = $nameserver->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('ns3.example.com', $array['name']);
        $this->assertEquals('10.0.0.1', $array['ip']);
        $this->assertEquals('fe80::1', $array['ip6']);
    }

    public function testToArrayFiltersNullValues()
    {
        $nameserver = new Nameserver();
        $nameserver->name = 'ns4.example.com';

        $array = $nameserver->toArray();

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('ip', $array);
        $this->assertArrayNotHasKey('ip6', $array);
    }

    public function testIPv4Validation()
    {
        $nameserver = new Nameserver();
        $nameserver->ip = '192.168.1.1';

        $this->assertMatchesRegularExpression('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $nameserver->ip);
    }

    public function testIPv6Validation()
    {
        $nameserver = new Nameserver();
        $nameserver->ip6 = '2001:db8::1';

        $this->assertStringContainsString(':', $nameserver->ip6);
    }

    public function testNameserverWithOnlyName()
    {
        $nameserver = new Nameserver();
        $nameserver->name = 'ns.example.com';

        $array = $nameserver->toArray();

        $this->assertCount(1, $array);
        $this->assertEquals('ns.example.com', $array['name']);
    }

    public function testNameserverWithIPAddresses()
    {
        $data = [
            'name' => 'ns1.example.com',
            'ip' => '1.2.3.4',
            'ip6' => '::1',
        ];

        $nameserver = (new Nameserver())->fromArray($data);

        $this->assertEquals('1.2.3.4', $nameserver->ip);
        $this->assertEquals('::1', $nameserver->ip6);
    }

    public function testEmptyNameserver()
    {
        $nameserver = new Nameserver();

        $this->assertNull($nameserver->name);
        $this->assertNull($nameserver->ip);
        $this->assertNull($nameserver->ip6);
    }

    public function testFromArrayChaining()
    {
        $nameserver = (new Nameserver())
            ->fromArray(['name' => 'ns.test.com', 'ip' => '1.2.3.4']);

        $this->assertInstanceOf(Nameserver::class, $nameserver);
        $this->assertEquals('ns.test.com', $nameserver->name);
        $this->assertEquals('1.2.3.4', $nameserver->ip);
    }
}
