<?php

namespace Vexura\Tests\Domain;

use PHPUnit\Framework\TestCase;
use Vexura\OpenProvider;
use Vexura\Domain\Domain;

class DomainTest extends TestCase
{
    public function testCheck()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn([
                'data' => [
                    'results' => [
                        ['domain' => 'example.com', 'status' => 'free'],
                        ['domain' => 'example.net', 'status' => 'active'],
                    ]
                ]
            ]);

        $domain = new Domain($mockApi);
        $results = $domain->check(['example.com', 'example.net']);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertInstanceOf(\Vexura\Model\CheckDomainResult::class, $results[0]);
        $this->assertEquals('free', $results[0]->status);
        $this->assertEquals('active', $results[1]->status);
    }

    public function testGetAuthCode(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'auth_code' => '12345',
                    'success' => true,
                    'type' => 'external',
                ]
            ]);
        $domain = new Domain($mockApi);
        $resp = $domain->getAuthCode(1);
        $this->assertInstanceOf(\Vexura\Model\AuthCode::class, $resp);
        $this->assertSame('12345', $resp->auth_code);
        $this->assertTrue($resp->success);
    }

    public function testResetAuthCode(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn([
                'data' => [
                    'auth_code' => '67890',
                    'success' => true,
                    'type' => 'internal',
                ]
            ]);
        $domain = new Domain($mockApi);
        $req = new \Vexura\Model\DomainResetAuthCodeRequest();
        $req->authCodeType = 'internal';
        $resp = $domain->resetAuthCode(1, $req);
        $this->assertInstanceOf(\Vexura\Model\AuthCode::class, $resp);
        $this->assertSame('67890', $resp->auth_code);
        $this->assertTrue($resp->success);
    }

    public function testGetDomainUnauthorized401(): void
    {
        $client = new \GuzzleHttp\Client([
            'handler' => \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(200, [], json_encode(['data' => ['token' => 'test']])),
                new \GuzzleHttp\Psr7\Response(401, [], json_encode(['desc' => 'Unauthorized'])),
            ])),
            'http_errors' => false,
        ]);
        $op = new OpenProvider('u','p',$client);
        $domain = new Domain($op);
        $this->expectException(\Vexura\Exception\AuthenticationException::class);
        $domain->getDomain(1);
    }

    public function testGetDomainHydration(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'id' => 1,
                    'domain' => ['name' => 'example', 'extension' => 'com'],
                    'owner' => ['company_name' => 'ACME', 'full_name' => 'John Doe'],
                    'name_servers' => [['name' => 'ns1.example.com']],
                    'dnssec_keys' => [['alg' => 8, 'flags' => 257, 'protocol' => 3, 'pub_key' => 'abc', 'readonly' => 1]],
                    'abuse_details' => ['abuse_id' => 10, 'is_domain_held' => false, 'message' => ''],
                ]
            ]);
        $domain = new Domain($mockApi);
        $dto = $domain->getDomain(1);
        $this->assertInstanceOf(\Vexura\Model\Domain::class, $dto);
        $this->assertInstanceOf(\Vexura\Model\DomainName::class, $dto->domain);
        $this->assertSame('example', $dto->domain->name);
        $this->assertInstanceOf(\Vexura\Model\DomainOwner::class, $dto->owner);
        $this->assertInstanceOf(\Vexura\Model\Nameserver::class, $dto->name_servers[0]);
        $this->assertInstanceOf(\Vexura\Model\DomainDnssecKey::class, $dto->dnssec_keys[0]);
        $this->assertInstanceOf(\Vexura\Model\DomainAbuseDetails::class, $dto->abuse_details);
    }

    public function testAdditionalDataSpecs(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    ['name' => 'idnScript', 'type' => 'select', 'required' => false],
                ]
            ]);
        $domain = new Domain($mockApi);
        $specs = $domain->getAdditionalData(['domain.extension' => 'eu']);
        $this->assertIsArray($specs);
        $this->assertInstanceOf(\Vexura\Model\AdditionalDataSpecification::class, $specs[0]);
    }

    public function testCustomerAdditionalDataSpecs(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    ['name' => 'country_of_citizenship', 'type' => 'text', 'required' => false],
                ]
            ]);
        $domain = new Domain($mockApi);
        $specs = $domain->getCustomerAdditionalData(['domain.extension' => 'eu']);
        $this->assertIsArray($specs);
        $this->assertInstanceOf(\Vexura\Model\AdditionalDataSpecification::class, $specs[0]);
    }

    public function testCreateDomainRequestPayload(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo('domains'),
                $this->callback(function ($payload) {
                    $this->assertEquals('HANDLE1', $payload['owner_handle']);
                    $this->assertEquals('example', $payload['domain']['name']);
                    $this->assertEquals('com', $payload['domain']['extension']);
                    $this->assertEquals(1, $payload['period']);
                    return true;
                })
            )
            ->willReturn(['data' => ['id' => 10]]);

        $domain = new Domain($mockApi);
        $req = new \Vexura\Model\DomainCreateRequest('HANDLE1', (new \Vexura\Model\DomainName())->fromArray(['name' => 'example','extension' => 'com']), 1);
        $domain->createDomain($req);
    }

    public function testUpdateDomainRequestPayload(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->expects($this->once())
            ->method('put')
            ->with(
                $this->equalTo('domains/10'),
                $this->callback(function ($payload) {
                    $this->assertEquals('off', $payload['autorenew']);
                    return true;
                })
            )
            ->willReturn(['data' => ['id' => 10]]);

        $domain = new Domain($mockApi);
        $req = new \Vexura\Model\DomainUpdateRequest();
        $req->autorenew = 'off';
        $domain->updateDomain(10, $req);
    }
}
