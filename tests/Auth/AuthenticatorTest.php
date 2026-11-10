<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Auth;

use Ashio\OpenProvider\Auth\AccessToken;
use Ashio\OpenProvider\Auth\Authenticator;
use Ashio\OpenProvider\Auth\Credentials;
use Ashio\OpenProvider\Auth\InMemoryTokenStore;
use Ashio\OpenProvider\Auth\Psr16TokenStore;
use Ashio\OpenProvider\Auth\Secret;
use Ashio\OpenProvider\Auth\TokenStore;
use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Client\Environment;
use Ashio\OpenProvider\Exception\AuthenticationException;
use Ashio\OpenProvider\Exception\UnexpectedResponseException;
use Ashio\OpenProvider\Http\HttpTransport;
use Ashio\OpenProvider\Tests\Support\ArrayCache;
use Ashio\OpenProvider\Tests\Support\FakeHttpClient;
use Ashio\OpenProvider\Tests\Support\FrozenClock;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AuthenticatorTest extends TestCase
{
    private FakeHttpClient $http;
    private FrozenClock $clock;
    private InMemoryTokenStore $store;

    protected function setUp(): void
    {
        $this->http = new FakeHttpClient();
        $this->clock = new FrozenClock();
        $this->store = new InMemoryTokenStore();
    }

    public function testLogsInOnlyWhenATokenIsFirstNeeded(): void
    {
        $authenticator = $this->authenticator();
        self::assertSame([], $this->http->targets());

        $this->http->login('tok-a', 42);
        $token = $authenticator->token();

        self::assertSame('tok-a', $token->token->reveal());
        self::assertSame(42, $token->resellerId);
        self::assertSame(['POST /v1/auth/login'], $this->http->targets());
        $login = $this->http->requests[0];
        self::assertSame('{"username":"reseller","password":"pw"}', (string) $login->getBody());
        self::assertSame('application/json', $login->getHeaderLine('Content-Type'));
        self::assertSame('', $login->getHeaderLine('Authorization'));
    }

    public function testSendsTheConfiguredIp(): void
    {
        $this->http->login();

        $this->authenticator(new ClientConfig(Environment::Sandbox, ip: '0.0.0.0'))->token();

        self::assertSame('{"username":"reseller","password":"pw","ip":"0.0.0.0"}', (string) $this->http->requests[0]->getBody());
    }

    public function testReusesTheStoredTokenUntilTheRefreshPoint(): void
    {
        $authenticator = $this->authenticator();
        $this->http->login('tok-a')->login('tok-b');

        self::assertSame('tok-a', $authenticator->token()->token->reveal());
        $this->clock->advance(Authenticator::REFRESH_AFTER_SECONDS - 1);
        self::assertSame('tok-a', $authenticator->token()->token->reveal());
        $this->clock->advance(1);
        self::assertSame('tok-b', $authenticator->token()->token->reveal());
        self::assertCount(2, $this->http->requests);
    }

    public function testATokenSavedByAnotherProcessIsReused(): void
    {
        $first = $this->authenticator();
        $this->http->login('tok-shared');
        $first->token();

        $second = $this->authenticator();

        self::assertSame('tok-shared', $second->token()->token->reveal());
        self::assertCount(1, $this->http->requests);
    }

    public function testInvalidateOnlyDropsTheStaleToken(): void
    {
        $authenticator = $this->authenticator();
        $this->http->login('tok-a');
        $stale = new AccessToken(new Secret('tok-old'), null, $this->clock->now()->modify('+1 hour'));
        $current = $authenticator->token();

        $authenticator->invalidate($stale);
        self::assertSame('tok-a', $authenticator->token()->token->reveal());

        $authenticator->invalidate($current);
        $this->http->login('tok-b');
        self::assertSame('tok-b', $authenticator->token()->token->reveal());
    }

    public function testTokensAreKeptApartPerEnvironmentAndUser(): void
    {
        $this->http->login('tok-sandbox')->login('tok-production')->login('tok-other-user');

        $this->authenticator(ClientConfig::sandbox())->token();
        $this->authenticator(ClientConfig::production())->token();
        $this->authenticator(ClientConfig::sandbox(), new Credentials('someone-else', 'pw'))->token();

        self::assertCount(3, $this->http->requests);
        self::assertSame('tok-sandbox', $this->authenticator(ClientConfig::sandbox())->token()->token->reveal());
    }

    public function testARejectedLoginIsAnAuthenticationError(): void
    {
        $this->http->reply(['code' => 196, 'desc' => 'Authentication/Authorization Failed', 'data' => ''], 401);

        $this->expectException(AuthenticationException::class);

        $this->authenticator()->token();
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function loginDataWithoutAToken(): iterable
    {
        yield 'no token' => [['reseller_id' => 1]];
        yield 'empty token' => [['token' => '', 'reseller_id' => 1]];
        yield 'token that is not a string' => [['token' => 5]];
        yield 'data that is not an object' => ['tok'];
    }

    #[DataProvider('loginDataWithoutAToken')]
    public function testALoginWithoutATokenIsAnUnexpectedResponse(mixed $data): void
    {
        $this->http->reply(['code' => 0, 'data' => $data]);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The login response has no token');

        $this->authenticator()->token();
    }

    public function testAResellerIdThatIsNotAnIntegerIsLeftOut(): void
    {
        $this->http->reply(['code' => 0, 'data' => ['token' => 'tok', 'reseller_id' => '7']]);

        self::assertNull($this->authenticator()->token()->resellerId);
    }

    public function testThePsr16StoreKeepsTheTokenUntilItsRefreshPoint(): void
    {
        $cache = new ArrayCache();
        $store = new Psr16TokenStore($cache, $this->clock);
        $token = new AccessToken(new Secret('tok-a'), 9, $this->clock->now()->modify('+100 seconds'));

        $store->save('k', $token);
        $loaded = $store->get('k');

        self::assertNotNull($loaded);
        self::assertSame('tok-a', $loaded->token->reveal());
        self::assertSame(9, $loaded->resellerId);
        self::assertEquals($token->expiresAt, $loaded->expiresAt);
        self::assertSame([Psr16TokenStore::cacheKey('k') => 100], $cache->ttls);

        $store->delete('k');
        self::assertNull($store->get('k'));
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function malformedEntries(): iterable
    {
        $expires = '2030-01-01T00:00:00+00:00';
        yield 'token that is not a string' => [['token' => 5, 'expires_at' => $expires]];
        yield 'empty token' => [['token' => '', 'expires_at' => $expires]];
        yield 'expiry that is not a string' => [['token' => 't', 'expires_at' => 5]];
        yield 'expiry that is not a date' => [['token' => 't', 'expires_at' => 'soon']];
        yield 'reseller id that is not an integer' => [['token' => 't', 'expires_at' => $expires, 'reseller_id' => '7']];
        yield 'not an array' => ['t'];
    }

    #[DataProvider('malformedEntries')]
    public function testThePsr16StoreIgnoresAndDropsMalformedEntries(mixed $entry): void
    {
        $cache = new ArrayCache();
        $cache->set(Psr16TokenStore::cacheKey('k'), $entry);
        $store = new Psr16TokenStore($cache, $this->clock);

        self::assertNull($store->get('k'));
        self::assertFalse($cache->has(Psr16TokenStore::cacheKey('k')));
    }

    public function testThePsr16StoreReadsAnEntryWithoutAResellerId(): void
    {
        $cache = new ArrayCache();
        $cache->set(Psr16TokenStore::cacheKey('k'), ['token' => 't', 'expires_at' => '2030-01-01T00:00:00+00:00', 'reseller_id' => null]);

        $token = (new Psr16TokenStore($cache, $this->clock))->get('k');

        self::assertSame('t', $token?->token->reveal());
        self::assertNull($token->resellerId);
    }

    public function testThePsr16StoreUsesKeysEveryCacheAccepts(): void
    {
        $cache = new ArrayCache(); // refuses keys PSR-16 does not guarantee
        $store = new Psr16TokenStore($cache, $this->clock);
        $token = new AccessToken(new Secret('tok-a'), 9, $this->clock->now()->modify('+100 seconds'));
        $keys = [hash('sha256', "production\0reseller@example.com"), 'k', str_repeat('é', 40), ''];

        foreach ($keys as $key) {
            $store->save($key, $token);
            self::assertSame('tok-a', $store->get($key)?->token->reveal());
            self::assertSame(64, strlen(Psr16TokenStore::cacheKey($key)));
            self::assertStringStartsWith(Psr16TokenStore::PREFIX, Psr16TokenStore::cacheKey($key));
        }
        self::assertCount(4, $cache->values, 'one entry per key');
    }

    public function testThePsr16StoreDoesNotSaveAnExpiredToken(): void
    {
        $cache = new ArrayCache();
        (new Psr16TokenStore($cache, $this->clock))->save('k', new AccessToken(new Secret('t'), null, $this->clock->now()));

        self::assertSame([], $cache->values);
    }

    private function authenticator(?ClientConfig $config = null, ?Credentials $credentials = null, ?TokenStore $store = null): Authenticator
    {
        $config ??= ClientConfig::sandbox();
        $factory = new HttpFactory();

        return new Authenticator(
            $config,
            $credentials ?? new Credentials('reseller', 'pw'),
            new HttpTransport($this->http, $factory, $factory, $config->userAgent),
            $store ?? $this->store,
            $this->clock,
        );
    }
}
