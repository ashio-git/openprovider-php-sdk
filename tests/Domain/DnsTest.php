<?php

namespace Ashio\Tests\Domain;

use Ashio\Domain\DNS;
use Ashio\Model\DnsCreateZoneRequest;
use Ashio\Model\DnsRecord as DnsRecordModel;
use Ashio\Model\DomainName;
use Ashio\Model\Zone as ZoneModel;
use Ashio\OpenProvider;
use PHPUnit\Framework\TestCase;

class DnsTest extends TestCase
{
    public function testGetZone()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'id' => 123,
                    'name' => 'example.com',
                ],
            ]);

        $dns = new DNS($mockApi);
        $result = $dns->getZone('example.com');

        $this->assertInstanceOf(ZoneModel::class, $result);
        $this->assertEquals(123, $result->id);
        $this->assertEquals('example.com', $result->name);
    }

    public function testListZones()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 2,
                    'results' => [
                        ['id' => 123, 'name' => 'example.com'],
                        ['id' => 456, 'name' => 'example.net'],
                    ],
                ],
            ]);

        $dns = new DNS($mockApi);
        $result = $dns->listZones();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(2, $result->total);
        $this->assertCount(2, $result->results);
        $this->assertInstanceOf(ZoneModel::class, $result->results[0]);
        $this->assertEquals(123, $result->results[0]->id);
    }

    public function testListZoneRecords()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['name' => 'www', 'type' => 'A', 'value' => '127.0.0.1'],
                    ],
                ],
            ]);

        $dns = new DNS($mockApi);
        $result = $dns->listZoneRecords('example.com');

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
        $this->assertCount(1, $result->results);
        $this->assertInstanceOf(DnsRecordModel::class, $result->results[0]);
        $this->assertEquals('www', $result->results[0]->name);
    }

    public function testGetZoneUnauthorized401()
    {
        $client = new \GuzzleHttp\Client([
            'handler' => \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(200, [], json_encode(['data' => ['token' => 'test']])),
                new \GuzzleHttp\Psr7\Response(401, [], json_encode(['desc' => 'Unauthorized'])),
            ])),
            'http_errors' => false,
        ]);
        $op = new OpenProvider('u', 'p', $client);
        $dns = new DNS($op);
        $this->expectException(\Ashio\Exception\AuthenticationException::class);
        $dns->getZone('example.com');
    }

    public function testCreateZoneReturnsDto()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn([
                'data' => [
                    'success' => true,
                ],
            ]);

        $dns = new DNS($mockApi);
        $domain = (new DomainName())->fromArray(['name' => 'example', 'extension' => 'com']);
        $request = new DnsCreateZoneRequest($domain);
        $request->type = 'master';
        $request->records = [];
        $resp = $dns->createZone($request);
        $this->assertInstanceOf(\Ashio\Model\DnsActionResponse::class, $resp);
        $this->assertTrue($resp->success);
    }

    public function testUpdateZone()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('put')
            ->willReturn(['data' => ['success' => true]]);

        $dns = new DNS($mockApi);
        $request = new \Ashio\Model\DnsUpdateZoneRequest();
        $result = $dns->updateZone('example.com', $request);

        $this->assertInstanceOf(\Ashio\Model\DnsActionResponse::class, $result);
    }

    public function testDeleteZone()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('delete')
            ->willReturn(['data' => ['success' => true]]);

        $dns = new DNS($mockApi);
        $result = $dns->deleteZone('example.com');

        $this->assertInstanceOf(\Ashio\Model\DnsActionResponse::class, $result);
    }

    public function testListNameservers()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['name' => 'ns1.example.com', 'ip' => '1.2.3.4'],
                    ],
                ],
            ]);

        $dns = new DNS($mockApi);
        $result = $dns->listNameservers();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
    }

    public function testCreateNameserver()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['name' => 'ns1.example.com']]);

        $dns = new DNS($mockApi);
        $ns = new \Ashio\Model\Nameserver();
        $ns->name = 'ns1.example.com';
        $result = $dns->createNameserver($ns);

        $this->assertInstanceOf(\Ashio\Model\Nameserver::class, $result);
    }

    public function testGetNameserver()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn(['data' => ['name' => 'ns1.example.com']]);

        $dns = new DNS($mockApi);
        $result = $dns->getNameserver('ns1.example.com');

        $this->assertInstanceOf(\Ashio\Model\Nameserver::class, $result);
    }

    public function testUpdateNameserver()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('put')
            ->willReturn(['data' => ['success' => true]]);

        $dns = new DNS($mockApi);
        $ns = new \Ashio\Model\Nameserver();
        $result = $dns->updateNameserver('ns1.example.com', $ns);

        $this->assertTrue($result);
    }

    public function testDeleteNameserver()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('delete')
            ->willReturn(['data' => ['success' => true]]);

        $dns = new DNS($mockApi);
        $result = $dns->deleteNameserver('ns1.example.com');

        $this->assertTrue($result);
    }

    public function testListNameserverGroups()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['name' => 'group1'],
                    ],
                ],
            ]);

        $dns = new DNS($mockApi);
        $result = $dns->listNameserverGroups();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
    }

    public function testCreateNameserverGroup()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['success' => true]]);

        $dns = new DNS($mockApi);
        $group = new \Ashio\Model\NameserverGroup();
        $result = $dns->createNameserverGroup($group);

        $this->assertTrue($result);
    }

    public function testGetNameserverGroup()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn(['data' => ['name' => 'group1']]);

        $dns = new DNS($mockApi);
        $result = $dns->getNameserverGroup('group1');

        $this->assertInstanceOf(\Ashio\Model\NameserverGroup::class, $result);
    }

    public function testUpdateNameserverGroup()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('put')
            ->willReturn(['data' => ['success' => true]]);

        $dns = new DNS($mockApi);
        $group = new \Ashio\Model\NameserverGroup();
        $result = $dns->updateNameserverGroup('group1', $group);

        $this->assertTrue($result);
    }

    public function testDeleteNameserverGroup()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('delete')
            ->willReturn(['data' => ['success' => true]]);

        $dns = new DNS($mockApi);
        $result = $dns->deleteNameserverGroup('group1');

        $this->assertTrue($result);
    }

    public function testListTemplates()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['id' => 1, 'name' => 'template1'],
                    ],
                ],
            ]);

        $dns = new DNS($mockApi);
        $result = $dns->listTemplates();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
    }

    public function testCreateTemplate()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->expects($this->once())
            ->method('post')
            ->willReturn(['data' => ['id' => 1]]);
        $mockApi->expects($this->once())
            ->method('get')
            ->willReturn(['data' => ['id' => 1, 'name' => 'template1']]);

        $dns = new DNS($mockApi);
        $request = new \Ashio\Model\DnsCreateTemplateRequest('template1');
        $result = $dns->createTemplate($request);

        $this->assertInstanceOf(\Ashio\Model\DnsTemplate::class, $result);
    }

    public function testGetTemplate()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn(['data' => ['id' => 1, 'name' => 'template1']]);

        $dns = new DNS($mockApi);
        $result = $dns->getTemplate(1);

        $this->assertInstanceOf(\Ashio\Model\DnsTemplate::class, $result);
    }

    public function testDeleteTemplate()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('delete')
            ->willReturn(['data' => ['success' => true]]);

        $dns = new DNS($mockApi);
        $result = $dns->deleteTemplate(1);

        $this->assertInstanceOf(\Ashio\Model\ActionResponse::class, $result);
    }

    public function testCreateDomainToken()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['token' => 'abc123']]);

        $dns = new DNS($mockApi);
        $request = new \Ashio\Model\DomainTokenRequest();
        $result = $dns->createDomainToken($request);

        $this->assertInstanceOf(\Ashio\Model\DomainToken::class, $result);
    }
}
