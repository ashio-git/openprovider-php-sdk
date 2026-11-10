<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Smoke;

use Ashio\OpenProvider\Smoke\Support\SingleLoginHttpClient;
use Ashio\OpenProvider\Tests\Support\FakeHttpClient;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

/**
 * The production smoke suite logs in once per run. Whatever the first login answered, a second
 * one is refused before it is sent, because repeated failed logins can block the account.
 */
final class SingleLoginHttpClientTest extends TestCase
{
    private const LOGIN = 'https://api.openprovider.eu/v1/auth/login';

    public function testTheFirstLoginAndEveryReadGoThrough(): void
    {
        $http = (new FakeHttpClient())->reply(['code' => 196])->reply(['code' => 0])->reply(['code' => 0]);
        $client = new SingleLoginHttpClient($http);

        $client->sendRequest(new Request('POST', self::LOGIN));
        $client->sendRequest(new Request('GET', 'https://api.openprovider.eu/v1/domains'));
        $client->sendRequest(new Request('GET', 'https://api.openprovider.eu/v1/tlds'));

        self::assertSame(['POST /v1/auth/login', 'GET /v1/domains', 'GET /v1/tlds'], $http->targets());
    }

    public function testASecondLoginIsRefusedBeforeItIsSent(): void
    {
        $http = (new FakeHttpClient())->reply(['code' => 196]);
        $client = new SingleLoginHttpClient($http);
        $client->sendRequest(new Request('POST', self::LOGIN));

        try {
            $client->sendRequest(new Request('post', self::LOGIN));
            self::fail('a second login went through');
        } catch (\LogicException $e) {
            self::assertStringStartsWith('The production smoke suite logs in once per run', $e->getMessage());
        }

        self::assertSame(['POST /v1/auth/login'], $http->targets());
    }
}
