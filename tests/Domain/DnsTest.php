<?php

namespace Vexura\Tests\Domain;

use PHPUnit\Framework\TestCase;
use Vexura\OpenProvider;
use Vexura\Domain\DNS;
use Vexura\Model\Zone as ZoneModel;
use Vexura\Model\DnsRecord as DnsRecordModel;

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
                ]
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
                    ]
                ]
            ]);

        $dns = new DNS($mockApi);
        $result = $dns->listZones();

        $this->assertInstanceOf(\Vexura\Model\PaginatedResponse::class, $result);
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
                    ]
                ]
            ]);

        $dns = new DNS($mockApi);
        $result = $dns->listZoneRecords('example.com');

        $this->assertInstanceOf(\Vexura\Model\PaginatedResponse::class, $result);
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
        $op = new OpenProvider('u','p',$client);
        $dns = new DNS($op);
        $this->expectException(\Vexura\Exception\AuthenticationException::class);
        $dns->getZone('example.com');
    }

    public function testCreateZoneReturnsDto()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn([
                'data' => [
                    'success' => true,
                ]
            ]);

        $dns = new DNS($mockApi);
        $resp = $dns->createZone(['domain' => ['name' => 'example', 'extension' => 'com'], 'type' => 'master', 'records' => []]);
        $this->assertInstanceOf(\Vexura\Model\DnsActionResponse::class, $resp);
        $this->assertTrue($resp->success);
    }
}
