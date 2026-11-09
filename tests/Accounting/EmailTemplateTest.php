<?php

namespace Ashio\Tests\Accounting;

use Ashio\Accounting\EmailTemplate as EmailTemplateHandler;
use Ashio\Exception\NotFoundException;
use Ashio\Exception\ValidationException;
use Ashio\Model\EmailTemplate as EmailTemplateModel;
use Ashio\Model\PaginatedResponse;
use Ashio\OpenProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class EmailTemplateTest extends TestCase
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

    public function testListEmailsHappyPath(): void
    {
        $listPayload = [
            'data' => [
                'total' => 1,
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Welcome',
                        'group' => 'ive',
                        'is_active' => true,
                        'is_default' => false,
                        'locale' => ['en_US'],
                        'fields' => [ ['name' => 'senderEmail', 'value' => 'support@example.com'] ],
                        'tags' => [ ['key' => 'customer', 'value' => 'VIP'] ],
                    ],
                ],
            ],
        ];

        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($listPayload)),
        ]);

        $api = new OpenProvider('u', 'p', httpClient: $client);
        $handler = new EmailTemplateHandler($api);
        $resp = $handler->listEmails();

        $this->assertInstanceOf(PaginatedResponse::class, $resp);
        $this->assertSame(1, $resp->total);
        $this->assertInstanceOf(EmailTemplateModel::class, $resp->results[0]);
        $this->assertSame('Welcome', $resp->results[0]->name);
    }

    public function testListEmailsValidationError(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(422, [], json_encode(['desc' => 'Invalid'])),
        ]);

        $api = new OpenProvider('u', 'p', httpClient: $client);
        $handler = new EmailTemplateHandler($api);

        $this->expectException(ValidationException::class);
        $handler->listEmails(-1);
    }

    public function testUpdateEmailNotFound(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(404, [], json_encode(['desc' => 'Not found'])),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $handler = new EmailTemplateHandler($api);

        $this->expectException(NotFoundException::class);
        $req = new \Ashio\Model\EmailTemplateUpdateRequest();
        $req->name = 'X';
        $handler->updateEmail(9999, $req);
    }
}
