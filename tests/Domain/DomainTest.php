<?php

namespace Ashio\Tests\Domain;

use Ashio\Domain\Domain;
use Ashio\OpenProvider;
use PHPUnit\Framework\TestCase;

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
                    ],
                ],
            ]);

        $domain = new Domain($mockApi);
        $results = $domain->check(['example.com', 'example.net']);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertInstanceOf(\Ashio\Model\CheckDomainResult::class, $results[0]);
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
                ],
            ]);
        $domain = new Domain($mockApi);
        $resp = $domain->getAuthCode(1);
        $this->assertInstanceOf(\Ashio\Model\AuthCode::class, $resp);
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
                ],
            ]);
        $domain = new Domain($mockApi);
        $req = new \Ashio\Model\DomainResetAuthCodeRequest();
        $req->authCodeType = 'internal';
        $resp = $domain->resetAuthCode(1, $req);
        $this->assertInstanceOf(\Ashio\Model\AuthCode::class, $resp);
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
        $op = new OpenProvider('u', 'p', $client);
        $domain = new Domain($op);
        $this->expectException(\Ashio\Exception\AuthenticationException::class);
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
                ],
            ]);
        $domain = new Domain($mockApi);
        $dto = $domain->getDomain(1);
        $this->assertInstanceOf(\Ashio\Model\Domain::class, $dto);
        $this->assertInstanceOf(\Ashio\Model\DomainName::class, $dto->domain);
        $this->assertSame('example', $dto->domain->name);
        $this->assertInstanceOf(\Ashio\Model\DomainOwner::class, $dto->owner);
        $this->assertInstanceOf(\Ashio\Model\Nameserver::class, $dto->name_servers[0]);
        $this->assertInstanceOf(\Ashio\Model\DomainDnssecKey::class, $dto->dnssec_keys[0]);
        $this->assertInstanceOf(\Ashio\Model\DomainAbuseDetails::class, $dto->abuse_details);
    }

    public function testAdditionalDataSpecs(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    ['name' => 'idnScript', 'type' => 'select', 'required' => false],
                ],
            ]);
        $domain = new Domain($mockApi);
        $specs = $domain->getAdditionalData(['domain.extension' => 'eu']);
        $this->assertIsArray($specs);
        $this->assertInstanceOf(\Ashio\Model\AdditionalDataSpecification::class, $specs[0]);
    }

    public function testCustomerAdditionalDataSpecs(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    ['name' => 'country_of_citizenship', 'type' => 'text', 'required' => false],
                ],
            ]);
        $domain = new Domain($mockApi);
        $specs = $domain->getCustomerAdditionalData(['domain.extension' => 'eu']);
        $this->assertIsArray($specs);
        $this->assertInstanceOf(\Ashio\Model\AdditionalDataSpecification::class, $specs[0]);
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
        $req = new \Ashio\Model\DomainCreateRequest('HANDLE1', (new \Ashio\Model\DomainName())->fromArray(['name' => 'example','extension' => 'com']), 1);
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
        $req = new \Ashio\Model\DomainUpdateRequest();
        $req->autorenew = 'off';
        $domain->updateDomain(10, $req);
    }

    public function testDeleteDomain(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('delete')
            ->willReturn(['data' => ['status' => 'deleted']]);

        $domain = new Domain($mockApi);
        $result = $domain->deleteDomain(10);

        $this->assertInstanceOf(\Ashio\Model\DeleteDomainResponse::class, $result);
    }

    public function testRenewDomain(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['id' => 10, 'renewed' => true]]);

        $domain = new Domain($mockApi);
        $req = new \Ashio\Model\RenewDomainRequest();
        $req->period = 1;
        $result = $domain->renewDomain(10, $req);

        $this->assertInstanceOf(\Ashio\Model\RenewDomainResponse::class, $result);
    }

    public function testRestoreDomain(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['id' => 10, 'restored' => true]]);

        $domain = new Domain($mockApi);
        $req = new \Ashio\Model\DomainRestoreRequest();
        $result = $domain->restoreDomain(10, $req);

        $this->assertInstanceOf(\Ashio\Model\RestoreDomainResponse::class, $result);
    }

    public function testApproveTransfer(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['success' => true]]);

        $domain = new Domain($mockApi);
        $req = new \Ashio\Model\DomainApproveTransferRequest();
        $result = $domain->approveTransfer(10, $req);

        $this->assertInstanceOf(\Ashio\Model\ActionResponse::class, $result);
    }

    public function testTransferDomain(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['id' => 10, 'transferred' => true]]);

        $domain = new Domain($mockApi);
        $domainName = (new \Ashio\Model\DomainName())->fromArray(['name' => 'example', 'extension' => 'com']);
        $req = new \Ashio\Model\DomainTransferRequest($domainName);
        $result = $domain->transferDomain($req);

        $this->assertInstanceOf(\Ashio\Model\TransferDomainResponse::class, $result);
    }

    public function testTradeDomain(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['id' => 10, 'traded' => true]]);

        $domain = new Domain($mockApi);
        $domainName = (new \Ashio\Model\DomainName())->fromArray(['name' => 'example', 'extension' => 'com']);
        $req = new \Ashio\Model\DomainTradeRequest($domainName);
        $result = $domain->tradeDomain($req);

        $this->assertInstanceOf(\Ashio\Model\TradeDomainResponse::class, $result);
    }

    public function testSuggestName(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['results' => [['name' => 'example', 'extension' => 'com']]]]);

        $domain = new Domain($mockApi);
        $req = new \Ashio\Model\DomainSuggestNameRequest();
        $req->name = 'example';
        $result = $domain->suggestName($req);

        $this->assertIsArray($result);
    }

    public function testSendFoa1(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['success' => true]]);

        $domain = new Domain($mockApi);
        $req = new \Ashio\Model\DomainSendFoa1Request();
        $result = $domain->sendFoa1(10, $req);

        $this->assertInstanceOf(\Ashio\Model\ActionResponse::class, $result);
        $this->assertTrue($result->success);
    }

    public function testTryAgainLastOperation(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['success' => true]]);

        $domain = new Domain($mockApi);
        $req = new \Ashio\Model\DomainTryAgainLastOperationRequest();
        $result = $domain->tryAgainLastOperation(10, $req);

        $this->assertTrue($result);
    }

    public function testGetPricelist(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn(['data' => ['currency' => 'EUR']]);

        $domain = new Domain($mockApi);
        $result = $domain->getPricelist();

        $this->assertInstanceOf(\Ashio\Model\Price::class, $result);
    }

    public function testListDomains(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['id' => 1, 'domain' => ['name' => 'example', 'extension' => 'com']],
                    ],
                ],
            ]);

        $domain = new Domain($mockApi);
        $result = $domain->listDomains();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
    }
}
