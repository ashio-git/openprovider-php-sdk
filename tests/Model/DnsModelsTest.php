<?php

namespace Ashio\Tests\Model;

use Ashio\Model\Zone;
use Ashio\Model\DnsRecord;
use Ashio\Model\DnsTemplate;
use Ashio\Model\NameserverGroup;
use Ashio\Model\DomainToken;
use Ashio\Model\DomainTokenRequest;
use Ashio\Model\DnsCreateZoneRequest;
use Ashio\Model\DnsUpdateZoneRequest;
use Ashio\Model\DnsCreateTemplateRequest;
use PHPUnit\Framework\TestCase;

class DnsModelsTest extends TestCase
{
    public function testZoneConstruction()
    {
        $zone = new Zone();
        $zone->id = 123;
        $zone->name = 'example.com';
        $zone->type = 'master';
        $zone->active = true;
        
        $this->assertEquals(123, $zone->id);
        $this->assertEquals('example.com', $zone->name);
        $this->assertEquals('master', $zone->type);
        $this->assertTrue($zone->active);
    }

    public function testDnsRecordFromArray()
    {
        $data = [
            'name' => 'www',
            'type' => 'A',
            'value' => '192.168.1.1',
            'ttl' => 3600,
        ];
        
        $record = new DnsRecord($data);
        
        $this->assertEquals('www', $record->name);
        $this->assertEquals('A', $record->type);
        $this->assertEquals('192.168.1.1', $record->value);
        $this->assertEquals(3600, $record->ttl);
    }

    public function testDnsTemplateFromArray()
    {
        $data = [
            'id' => 456,
            'name' => 'Default Template',
        ];
        
        $template = (new DnsTemplate())->fromArray($data);
        
        $this->assertEquals(456, $template->id);
        $this->assertEquals('Default Template', $template->name);
    }

    public function testDnsTemplateToArray()
    {
        $template = new DnsTemplate();
        $template->id = 789;
        $template->name = 'Custom Template';
        
        $array = $template->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals(789, $array['id']);
        $this->assertEquals('Custom Template', $array['name']);
    }

    public function testNameserverGroupConstruction()
    {
        $group = new NameserverGroup();
        $group->name = 'group1';
        
        $this->assertEquals('group1', $group->name);
    }

    public function testDomainTokenFromArray()
    {
        $data = [
            'id' => 100,
            'token' => 'abc123xyz',
            'domain' => ['name' => 'example', 'extension' => 'com'],
        ];
        
        $token = (new DomainToken())->fromArray($data);
        
        $this->assertEquals(100, $token->id);
        $this->assertEquals('abc123xyz', $token->token);
        $this->assertInstanceOf(\Ashio\Model\DomainName::class, $token->domain);
    }

    public function testDomainTokenConstructorWithData()
    {
        $data = [
            'id' => 200,
            'token' => 'token123',
        ];
        
        $token = new DomainToken($data);
        
        $this->assertEquals(200, $token->id);
        $this->assertEquals('token123', $token->token);
    }

    public function testDomainTokenToArray()
    {
        $token = new DomainToken();
        $token->id = 300;
        $token->token = 'mytoken';
        
        $array = $token->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals(300, $array['id']);
        $this->assertEquals('mytoken', $array['token']);
    }

    public function testDomainTokenRequestToArray()
    {
        $domainName = (new \Ashio\Model\DomainName())->fromArray(['name' => 'test', 'extension' => 'com']);
        $request = new DomainTokenRequest($domainName, 'dns', '2025-12-31');
        
        $array = $request->toArray();
        
        $this->assertIsArray($array);
        $this->assertIsArray($array['domain']);
        $this->assertEquals('dns', $array['type']);
        $this->assertEquals('2025-12-31', $array['expiration_date']);
    }

    public function testDnsCreateZoneRequest()
    {
        $domainName = (new \Ashio\Model\DomainName())->fromArray(['name' => 'zone', 'extension' => 'com']);
        $request = new DnsCreateZoneRequest($domainName);
        $request->type = 'master';
        
        $array = $request->toArray();
        
        $this->assertIsArray($array);
        $this->assertIsArray($array['domain']);
        $this->assertEquals('master', $array['type']);
    }

    public function testDnsUpdateZoneRequest()
    {
        $request = new DnsUpdateZoneRequest();
        
        $array = $request->toArray();
        
        $this->assertIsArray($array);
    }

    public function testDnsCreateTemplateRequest()
    {
        $request = new DnsCreateTemplateRequest('My Template');
        
        $array = $request->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('My Template', $array['name']);
    }

    public function testEmptyZone()
    {
        $zone = new Zone();
        
        $this->assertNull($zone->id);
        $this->assertNull($zone->name);
    }

    public function testEmptyDnsTemplate()
    {
        $template = new DnsTemplate();
        
        $this->assertNull($template->id);
        $this->assertNull($template->name);
    }

    public function testDnsRecordWithAllFields()
    {
        $data = [
            'name' => 'mail',
            'type' => 'MX',
            'value' => 'mail.example.com',
            'ttl' => 7200,
            'prio' => 10,
        ];
        
        $record = new DnsRecord($data);
        
        $this->assertEquals('mail', $record->name);
        $this->assertEquals('MX', $record->type);
        $this->assertEquals(10, $record->prio);
    }
}
