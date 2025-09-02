<?php

namespace Vexura\Tests\Domain;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Vexura\Domain\EasyDMARC;
use Vexura\Exception\NotFoundException;
use Vexura\Exception\ValidationException;
use Vexura\Model\EasyDmarcOrder;
use Vexura\Model\PaginatedResponse;
use Vexura\OpenProvider;

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

        $api = new OpenProvider('u','p',$client);
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
        $api = new OpenProvider('u','p',$client);
        $handler = new EasyDMARC($api);
        $this->expectException(ValidationException::class);
        $domain = new \Vexura\Model\DomainName();
        $req = new \Vexura\Model\EasyDmarcCreateRequest($domain, '');
        $handler->createEasyDmarc($req);
    }

    public function testGetSsoNotFound(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(404, [], json_encode(['desc' => 'Not found'])),
        ]);
        $api = new OpenProvider('u','p',$client);
        $handler = new EasyDMARC($api);
        $this->expectException(NotFoundException::class);
        $handler->getEasyDmarcSso(999);
    }
}

