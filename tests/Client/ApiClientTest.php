<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Client;

use Ashio\OpenProvider\Auth\Credentials;
use Ashio\OpenProvider\Auth\InMemoryTokenStore;
use Ashio\OpenProvider\Client\ApiClient;
use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Client\Environment;
use Ashio\OpenProvider\Client\RetryPolicy;
use Ashio\OpenProvider\Exception\AuthenticationException;
use Ashio\OpenProvider\Exception\InvalidArgumentException;
use Ashio\OpenProvider\Exception\MaintenanceException;
use Ashio\OpenProvider\Exception\RateLimitedException;
use Ashio\OpenProvider\Exception\TransportException;
use Ashio\OpenProvider\Exception\UnexpectedResponseException;
use Ashio\OpenProvider\Sdk;
use Ashio\OpenProvider\Spec\OperationSpec;
use Ashio\OpenProvider\Tests\Support\FakeHttpClient;
use Ashio\OpenProvider\Tests\Support\FrozenClock;
use Ashio\OpenProvider\Tests\Support\RecordingSleeper;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ApiClientTest extends TestCase
{
    private FakeHttpClient $http;
    private RecordingSleeper $sleeper;

    protected function setUp(): void
    {
        $this->http = new FakeHttpClient();
        $this->sleeper = new RecordingSleeper();
    }

    public function testCreatingAClientDoesNoIo(): void
    {
        $this->client();

        self::assertSame([], $this->http->requests);
    }

    public function testTheFirstCallLogsInAndSendsTheBearerToken(): void
    {
        $this->http->login('tok-1')->reply(['code' => 0, 'desc' => '', 'data' => ['results' => []]]);

        $response = $this->client()->call(self::listDomains(), ['limit' => 10]);

        self::assertSame(['results' => []], $response->data);
        self::assertSame(['POST /v1/auth/login', 'GET /v1/domains?limit=10'], $this->http->targets());
        $call = $this->http->requests[1];
        self::assertSame('https', $call->getUri()->getScheme());
        self::assertSame('api.sandbox.openprovider.nl', $call->getUri()->getHost());
        self::assertSame('Bearer tok-1', $call->getHeaderLine('Authorization'));
        self::assertSame('ashio-openprovider-php/' . Sdk::VERSION, $call->getHeaderLine('User-Agent'));
        self::assertSame('application/json', $call->getHeaderLine('Accept'));
        self::assertSame('', $call->getHeaderLine('Content-Type'));
    }

    public function testTheTokenIsReusedAcrossCalls(): void
    {
        $this->http->login()->reply(['code' => 0])->reply(['code' => 0])->reply(['code' => 0]);
        $client = $this->client();

        $client->call(self::listDomains());
        $client->call(self::listDomains());
        $client->call(self::createDomain(), ['body' => ['name' => 'x']]);

        self::assertSame(1, $this->http->count('POST /v1/auth/login'));
        self::assertSame('{"name":"x"}', (string) $this->http->requests[3]->getBody());
        self::assertSame('application/json', $this->http->requests[3]->getHeaderLine('Content-Type'));
    }

    /**
     * @return iterable<string, array{OperationSpec, array<string, mixed>, string}>
     */
    public static function operations(): iterable
    {
        yield 'read' => [self::listDomains(), [], 'GET /v1/domains'];
        yield 'write' => [self::createDomain(), ['body' => ['name' => 'x']], 'POST /v1/domains'];
    }

    /**
     * On code 196 the client logs in again exactly once and repeats the call once, for reads
     * and writes alike: a 196 means the call was not executed.
     *
     * @param array<string, mixed> $arguments
     */
    #[DataProvider('operations')]
    public function testAnExpiredTokenTriggersExactlyOneNewLogin(OperationSpec $operation, array $arguments, string $target): void
    {
        $this->http->login('tok-old')
            ->reply(['code' => 196, 'desc' => 'Authentication/Authorization Failed'], 500)
            ->login('tok-new')
            ->reply(['code' => 0, 'data' => ['id' => 1]]);

        $response = $this->client()->call($operation, $arguments);

        self::assertSame(['id' => 1], $response->data);
        self::assertSame(['POST /v1/auth/login', $target, 'POST /v1/auth/login', $target], $this->http->targets());
        self::assertSame('Bearer tok-new', $this->http->requests[3]->getHeaderLine('Authorization'));
    }

    /**
     * @param array<string, mixed> $arguments
     */
    #[DataProvider('operations')]
    public function testASecond196IsAnErrorWithoutAThirdAttempt(OperationSpec $operation, array $arguments, string $target): void
    {
        $this->http->login()
            ->reply(['code' => 196, 'desc' => 'expired'], 401)
            ->login()
            ->reply(['code' => 196, 'desc' => 'still rejected'], 401);

        try {
            $this->client()->call($operation, $arguments);
            self::fail('expected an authentication error');
        } catch (AuthenticationException $e) {
            self::assertSame(196, $e->apiCode);
            self::assertSame(401, $e->httpStatus);
        }
        self::assertSame(2, $this->http->count('POST /v1/auth/login'));
        self::assertSame(2, $this->http->count($target));
        self::assertSame(0, $this->http->pending());
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function terminalAuthenticationCodes(): iterable
    {
        yield 'ip not allowed' => [10005];
        yield '2fa required' => [10006];
    }

    #[DataProvider('terminalAuthenticationCodes')]
    public function testIpAndTwoFactorRejectionsAreNeverRetried(int $code): void
    {
        $this->http->login()->reply(['code' => $code, 'desc' => 'denied'], 500);

        try {
            $this->client(new RetryPolicy(3))->call(self::listDomains());
            self::fail('expected an authentication error');
        } catch (AuthenticationException $e) {
            self::assertSame($code, $e->apiCode);
        }
        self::assertSame(['POST /v1/auth/login', 'GET /v1/domains'], $this->http->targets());
    }

    public function testAPublicOperationSendsNoTokenAndNeverLogsIn(): void
    {
        $signup = new OperationSpec('POST', '/resellers/signup', 'CreateResellerPublic', [], requiresAuth: false);
        $this->http->reply(['code' => 0, 'data' => []])->reply(['code' => 196, 'desc' => 'x'], 500);
        $client = $this->client();

        $client->call($signup);

        try {
            $client->call($signup);
            self::fail('expected an authentication error');
        } catch (AuthenticationException) {
        }

        self::assertSame(['POST /v1/resellers/signup', 'POST /v1/resellers/signup'], $this->http->targets());
        self::assertSame('', $this->http->requests[0]->getHeaderLine('Authorization'));
    }

    public function testSuccessIsTheBodyCodeEvenOnAnHttpError(): void
    {
        $this->http->login()->reply(['code' => 0, 'data' => 'ok'], 500);

        self::assertSame('ok', $this->client()->call(self::listDomains())->data);
    }

    /**
     * A write is sent once, whatever fails and whatever the retry policy says.
     */
    public function testWritesAreNeverRepeated(): void
    {
        $failures = [
            static fn (FakeHttpClient $h) => $h->fail(new ConnectException('timeout', new Request('POST', 'https://x'))),
            static fn (FakeHttpClient $h) => $h->replyRaw('<html>502</html>', 502),
            static fn (FakeHttpClient $h) => $h->reply(['code' => 4005, 'desc' => 'API disabled for maintenance'], 500),
            static fn (FakeHttpClient $h) => $h->reply(['code' => 4007, 'desc' => 'blocked'], 500),
            static fn (FakeHttpClient $h) => $h->reply(['code' => 10, 'desc' => 'registry'], 500),
        ];
        foreach ($failures as $index => $failure) {
            $this->http = new FakeHttpClient();
            $this->http->login();
            $failure($this->http);
            $this->http->reply(['code' => 0]);

            try {
                $this->client(new RetryPolicy(3))->call(self::createDomain(), ['body' => ['name' => 'x']]);
                self::fail("failure $index: expected an exception");
            } catch (TransportException|UnexpectedResponseException|MaintenanceException|RateLimitedException) {
            }

            self::assertSame(1, $this->http->count('POST /v1/domains'), "failure $index");
            self::assertSame(1, $this->http->pending(), "failure $index");
            self::assertSame([], $this->sleeper->sleeps, "failure $index");
        }
    }

    public function testReadsAreNotRetriedByDefault(): void
    {
        $this->http->login()->fail(new ConnectException('timeout', new Request('GET', 'https://x')));

        $this->expectException(TransportException::class);

        $this->client()->call(self::listDomains());
    }

    public function testAReadIsRetriedWhenTheCallerOptsIn(): void
    {
        $this->http->login()
            ->fail(new ConnectException('timeout', new Request('GET', 'https://x')))
            ->replyRaw('<html>bad gateway</html>', 502)
            ->reply(['code' => 4005, 'desc' => 'maintenance'], 500)
            ->reply(['code' => 0, 'data' => 'ok']);

        $response = $this->client(new RetryPolicy(3, [10, 20]))->call(self::listDomains());

        self::assertSame('ok', $response->data);
        self::assertSame(4, $this->http->count('GET /v1/domains'));
        self::assertSame([10, 20, 20], $this->sleeper->sleeps);
    }

    public function testANonJsonHttp500IsTransientButA4xxIsNot(): void
    {
        $this->http->login()
            ->replyRaw('<html>internal error</html>', 500)
            ->reply(['code' => 0, 'data' => 'ok'])
            ->replyRaw('<html>not found</html>', 404);
        $client = $this->client(new RetryPolicy(1, [5]));

        self::assertSame('ok', $client->call(self::listDomains())->data);

        try {
            $client->call(self::listDomains());
            self::fail('expected an exception');
        } catch (UnexpectedResponseException $e) {
            self::assertSame(404, $e->httpStatus);
        }
        self::assertSame(3, $this->http->count('GET /v1/domains'));
    }

    public function testReadRetriesStopAtTheLimitAndRethrowTheLastError(): void
    {
        $this->http->login()
            ->replyRaw('<html>1</html>', 503)
            ->replyRaw('<html>2</html>', 504);

        try {
            $this->client(new RetryPolicy(1, [5]))->call(self::listDomains());
            self::fail('expected an exception');
        } catch (UnexpectedResponseException $e) {
            self::assertSame(504, $e->httpStatus);
        }
        self::assertSame(2, $this->http->count('GET /v1/domains'));
    }

    public function testReadsAreNotRetriedOnRateLimitsOrClientErrors(): void
    {
        $this->http->login()->reply(['code' => 4007, 'desc' => 'blocked'], 500);

        try {
            $this->client(new RetryPolicy(3))->call(self::listDomains());
            self::fail('expected an exception');
        } catch (RateLimitedException) {
        }
        self::assertSame(1, $this->http->count('GET /v1/domains'));
        self::assertSame([], $this->sleeper->sleeps);
    }

    public function testInvalidArgumentsFailBeforeAnyRequest(): void
    {
        try {
            $this->client()->call(self::listDomains(), ['lastNamePattern' => 'x']);
            self::fail('expected an exception');
        } catch (InvalidArgumentException) {
        }

        self::assertSame([], $this->http->requests);
    }

    public function testTheBasePathIsConfigurable(): void
    {
        $this->http->login()->reply(['code' => 0]);

        $this->client(config: new ClientConfig(Environment::Production, basePath: '/v1beta'))->call(self::listDomains());

        self::assertSame(['POST /v1beta/auth/login', 'GET /v1beta/domains'], $this->http->targets());
        self::assertSame('api.openprovider.eu', $this->http->requests[1]->getUri()->getHost());
    }

    private function client(?RetryPolicy $retry = null, ?ClientConfig $config = null): ApiClient
    {
        $config ??= new ClientConfig(Environment::Sandbox, getRetry: $retry ?? RetryPolicy::none());

        return ApiClient::create(
            $config,
            new Credentials('reseller', 'pw'),
            new InMemoryTokenStore(),
            $this->http,
            clock: new FrozenClock(),
            sleeper: $this->sleeper,
        );
    }

    private static function listDomains(): OperationSpec
    {
        return OperationSpec::fromSwagger('get', '/domains', [
            'operationId' => 'ListDomains',
            'parameters' => [['name' => 'limit', 'in' => 'query', 'type' => 'integer', 'format' => 'int32']],
        ]);
    }

    private static function createDomain(): OperationSpec
    {
        return OperationSpec::fromSwagger('post', '/domains', [
            'operationId' => 'CreateDomain',
            'parameters' => [['name' => 'body', 'in' => 'body', 'required' => true, 'schema' => []]],
        ]);
    }
}
