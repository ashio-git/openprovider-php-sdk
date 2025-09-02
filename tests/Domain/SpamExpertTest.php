<?php

namespace Vexura\Tests\Domain;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Vexura\Domain\SpamExpert;
use Vexura\Exception\NotFoundException;
use Vexura\Exception\ValidationException;
use Vexura\Model\SpamExpertDomain;
use Vexura\Model\SpamExpertCreateDomainRequest;
use Vexura\OpenProvider;

class SpamExpertTest extends TestCase
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

    public function testGetDomainHappyPath(): void
    {
        $payload = [
            'data' => [
                'id' => 10,
                'domain_name' => 'example.com',
                'is_active' => true,
            ],
        ];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u','p',$client);
        $handler = new SpamExpert($api);
        $domain = $handler->getDomain('example.com');
        $this->assertInstanceOf(SpamExpertDomain::class, $domain);
        $this->assertSame('example.com', $domain->domain_name);
    }

    public function testCreateDomainValidationError(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(422, [], json_encode(['desc' => 'Invalid'])),
        ]);
        $api = new OpenProvider('u','p',$client);
        $handler = new SpamExpert($api);
        $this->expectException(ValidationException::class);
        $handler->createDomain(new SpamExpertCreateDomainRequest(''));
    }

    public function testGetDomainNotFound(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(404, [], json_encode(['desc' => 'Not found'])),
        ]);
        $api = new OpenProvider('u','p',$client);
        $handler = new SpamExpert($api);
        $this->expectException(NotFoundException::class);
        $handler->getDomain('missing.com');
    }
}

