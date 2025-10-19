<?php

namespace Ashio\Tests\Domain;

use Ashio\Domain\EasyDMARC;
use Ashio\Exception\NotFoundException;
use Ashio\Exception\ValidationException;
use Ashio\Model\EasyDmarcOrder;
use Ashio\Model\PaginatedResponse;
use Ashio\OpenProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class EasyDmarcTest extends TestCase
{
    private function makeClient(array $responses): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);

        return new Client(['handler' => $handlerStack, 'http_errors' => false]);
    }

    private function loginOk(): Response
    {
        return new Response(200, [], json_encode(['data' => ['token' => 'test']])) ;
    }

    public function testListEasyDmarcHappyPath(): void
    {
        $payload = [
            'data' => [
                'total' => 1,
                'results' => [
                    [
                        'id' => 123,
                        'status' => 'active',
                        'domain' => ['name' => 'example', 'extension' => 'com'],
                    ],
                ],
            ],
        ];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);

        $api = new OpenProvider('u', 'p', $client);
        $handler = new EasyDMARC($api);
        $resp = $handler->listEasyDmarc();
        $this->assertInstanceOf(PaginatedResponse::class, $resp);
        $this->assertSame(1, $resp->total);
        $this->assertInstanceOf(EasyDmarcOrder::class, $resp->results[0]);
        $this->assertSame(123, $resp->results[0]->id);
    }

    public function testCreateEasyDmarcValidationError(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(422, [], json_encode(['desc' => 'Invalid'])),
        ]);
        $api = new OpenProvider('u', 'p', $client);
        $handler = new EasyDMARC($api);
        $this->expectException(ValidationException::class);
        $domain = new \Ashio\Model\DomainName();
        $req = new \Ashio\Model\EasyDmarcCreateRequest($domain, '');
        $handler->createEasyDmarc($req);
    }

    public function testGetSsoNotFound(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(404, [], json_encode(['desc' => 'Not found'])),
        ]);
        $api = new OpenProvider('u', 'p', $client);
        $handler = new EasyDMARC($api);
        $this->expectException(NotFoundException::class);
        $handler->getEasyDmarcSso(999);
    }

    public function testGetEasyDmarc(): void
    {
        $payload = ['data' => ['id' => 123, 'status' => 'active']];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', $client);
        $handler = new EasyDMARC($api);
        $result = $handler->getEasyDmarc(['id' => 123]);
        $this->assertInstanceOf(EasyDmarcOrder::class, $result);
        $this->assertEquals(123, $result->id);
    }

    public function testDeleteEasyDmarc(): void
    {
        $payload = ['data' => ['success' => true]];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', $client);
        $handler = new EasyDMARC($api);
        $result = $handler->deleteEasyDmarc(123);
        $this->assertTrue($result);
    }

    public function testRetryEasyDmarc(): void
    {
        $payload = ['data' => ['id' => 123, 'status' => 'active']];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', $client);
        $handler = new EasyDMARC($api);
        $req = new \Ashio\Model\EasyDmarcRetryRequest();
        $result = $handler->retryEasyDmarc(123, $req);
        $this->assertInstanceOf(EasyDmarcOrder::class, $result);
        $this->assertEquals(123, $result->id);
    }

    public function testGetEasyDmarcSso(): void
    {
        $payload = ['data' => ['url' => 'https://sso.example.com']];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', $client);
        $handler = new EasyDMARC($api);
        $result = $handler->getEasyDmarcSso(123);
        $this->assertIsString($result);
        $this->assertEquals('https://sso.example.com', $result);
    }
}
