<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Smoke;

use Ashio\OpenProvider\Smoke\Support\ReadOnlyHttpClient;
use Ashio\OpenProvider\Tests\Support\FakeHttpClient;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The production smoke suite runs against a real account, so its transport refuses every write
 * before it leaves the process: only GET goes out, plus the two POSTs that read (the login and the
 * domain check).
 */
final class ReadOnlyHttpClientTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function reads(): iterable
    {
        yield 'a GET' => ['GET', 'https://api.openprovider.eu/v1/domains?limit=5'];
        yield 'the login' => ['POST', 'https://api.openprovider.eu/v1/auth/login'];
        yield 'the domain check' => ['POST', 'https://api.openprovider.eu/v1/domains/check'];
    }

    #[DataProvider('reads')]
    public function testReadsGoThrough(string $method, string $uri): void
    {
        $http = (new FakeHttpClient())->reply(['code' => 0]);

        $response = (new ReadOnlyHttpClient($http))->sendRequest(new Request($method, $uri));

        self::assertSame(200, $response->getStatusCode());
        self::assertCount(1, $http->requests);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function writes(): iterable
    {
        yield 'a create' => ['POST', 'https://api.openprovider.eu/v1/domains'];
        yield 'an update' => ['PUT', 'https://api.openprovider.eu/v1/domains/123'];
        yield 'a delete' => ['DELETE', 'https://api.openprovider.eu/v1/dns/zones/example.com'];
        yield 'a patch' => ['PATCH', 'https://api.openprovider.eu/v1/customers/XX123456-XX'];
        yield 'a lowercase post' => ['post', 'https://api.openprovider.eu/v1/domains/transfer'];
        yield 'a path that only starts like a read' => ['POST', 'https://api.openprovider.eu/v1/domains/check/../123/renew'];
        yield 'a read path under another base' => ['POST', 'https://api.openprovider.eu/v2/auth/login'];
    }

    #[DataProvider('writes')]
    public function testWritesAreRefusedBeforeTheyAreSent(string $method, string $uri): void
    {
        $http = new FakeHttpClient();

        try {
            (new ReadOnlyHttpClient($http))->sendRequest(new Request($method, $uri));
            self::fail('the write went through');
        } catch (\LogicException $e) {
            self::assertStringStartsWith('The production smoke suite only reads; ' . strtoupper($method) . ' ', $e->getMessage());
        }

        self::assertSame([], $http->requests);
    }
}
