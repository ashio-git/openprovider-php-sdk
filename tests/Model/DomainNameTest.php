<?php

namespace Ashio\Tests\Model;

use Ashio\Model\DomainName;
use PHPUnit\Framework\TestCase;

class DomainNameTest extends TestCase
{
    public function testFromArrayWithValidData()
    {
        $data = [
            'name' => 'example',
            'extension' => 'com',
        ];

        $domainName = (new DomainName())->fromArray($data);

        $this->assertEquals('example', $domainName->name);
        $this->assertEquals('com', $domainName->extension);
    }

    public function testFromArrayWithMissingData()
    {
        $data = [];

        $domainName = (new DomainName())->fromArray($data);

        $this->assertNull($domainName->name);
        $this->assertNull($domainName->extension);
    }

    public function testToArrayWithValidData()
    {
        $domainName = new DomainName();
        $domainName->name = 'test';
        $domainName->extension = 'net';

        $array = $domainName->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('test', $array['name']);
        $this->assertEquals('net', $array['extension']);
    }

    public function testToArrayFiltersNullValues()
    {
        $domainName = new DomainName();
        $domainName->name = 'test';
        // extension is null

        $array = $domainName->toArray();

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('extension', $array);
    }

    public function testFullDomainString()
    {
        $domainName = new DomainName();
        $domainName->name = 'example';
        $domainName->extension = 'com';

        // Test that we can construct full domain
        $fullDomain = $domainName->name . '.' . $domainName->extension;
        $this->assertEquals('example.com', $fullDomain);
    }

    public function testFromArrayChaining()
    {
        $domainName = (new DomainName())
            ->fromArray(['name' => 'test', 'extension' => 'org']);

        $this->assertInstanceOf(DomainName::class, $domainName);
        $this->assertEquals('test', $domainName->name);
        $this->assertEquals('org', $domainName->extension);
    }

    public function testEmptyDomainName()
    {
        $domainName = new DomainName();

        $this->assertNull($domainName->name);
        $this->assertNull($domainName->extension);
    }

    public function testInternationalDomain()
    {
        $domainName = (new DomainName())->fromArray([
            'name' => 'münchen',
            'extension' => 'de',
        ]);

        $this->assertEquals('münchen', $domainName->name);
        $this->assertEquals('de', $domainName->extension);
    }
}
