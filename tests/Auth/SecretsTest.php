<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Auth;

use Ashio\OpenProvider\Auth\AccessToken;
use Ashio\OpenProvider\Auth\Credentials;
use Ashio\OpenProvider\Auth\InMemoryTokenStore;
use Ashio\OpenProvider\Auth\Secret;
use Ashio\OpenProvider\Client\ApiClient;
use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Exception\OpenProviderException;
use Ashio\OpenProvider\Spec\OperationSpec;
use Ashio\OpenProvider\Tests\Support\FakeHttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

/**
 * No secret in __toString, var_export or an exception message.
 */
final class SecretsTest extends TestCase
{
    private const PASSWORD = 'pw-9f2c1e7a-secret';
    private const TOKEN = 'tok-5d0b3a99-secret';

    public function testASecretNeverPrintsItsValue(): void
    {
        $secret = new Secret(self::PASSWORD);

        self::assertSame(self::PASSWORD, $secret->reveal());
        self::assertNoSecret((string) $secret);
        self::assertNoSecret(var_export($secret, true));
        self::assertNoSecret(print_r($secret, true));
        self::assertNoSecret(self::dump($secret));
        self::assertNoSecret((string) json_encode($secret));
        self::assertNoSecret(print_r((array) $secret, true));
        self::assertStringContainsString('[value] => ********', print_r($secret, true), 'dumps show the mask in place of the value');
        self::assertStringContainsString('resellerId=null', (string) new AccessToken($secret, null, new \DateTimeImmutable('2030-01-01')));
    }

    public function testASecretCannotBeSerialized(): void
    {
        $this->expectException(\LogicException::class);

        serialize(new Secret(self::PASSWORD));
    }

    public function testCredentialsAndTokensHideTheirSecrets(): void
    {
        $credentials = new Credentials('reseller-user', self::PASSWORD);
        $token = new AccessToken(new Secret(self::TOKEN), 7, new \DateTimeImmutable('2026-11-11 11:00:00 UTC'));

        foreach ([$credentials, $token] as $object) {
            self::assertNoSecret((string) $object);
            self::assertNoSecret(var_export($object, true));
            self::assertNoSecret(print_r($object, true));
            self::assertNoSecret(self::dump($object));
            self::assertNoSecret((string) json_encode($object));
        }
        self::assertStringContainsString('reseller-user', (string) $credentials);
    }

    public function testAWholeClientWithACachedTokenHidesBoth(): void
    {
        $http = (new FakeHttpClient())->login(self::TOKEN)->reply(['code' => 0, 'data' => []]);
        $client = ApiClient::create(ClientConfig::sandbox(), new Credentials('u', self::PASSWORD), new InMemoryTokenStore(), $http);
        $client->call(new OperationSpec('GET', '/tlds', 'ListTlds', []));
        $http->requests = []; // the fake's own log of what was sent; a real PSR-18 client keeps none

        self::assertNoSecret(var_export($client, true));
        self::assertNoSecret(print_r($client, true));
        self::assertNoSecret(self::dump($client));
    }

    public function testExceptionsAndTheirTracesCarryNoSecret(): void
    {
        $previous = ini_get('zend.exception_ignore_args');
        ini_set('zend.exception_ignore_args', '0');

        try {
            $failures = [
                (new FakeHttpClient())->reply(['code' => 196, 'desc' => 'Authentication/Authorization Failed', 'data' => ''], 500),
                (new FakeHttpClient())->replyRaw('<html>bad gateway</html>', 502),
                (new FakeHttpClient())->fail(new ConnectException('cURL error 7', new Request('POST', 'https://api.sandbox.openprovider.nl/v1/auth/login'))),
                (new FakeHttpClient())->login(self::TOKEN)->replyRaw('{"code":', 200, 'application/json'),
            ];
            foreach ($failures as $http) {
                $client = ApiClient::create(ClientConfig::sandbox(), new Credentials('u', self::PASSWORD), new InMemoryTokenStore(), $http);

                try {
                    $client->call(new OperationSpec('POST', '/domains', 'CreateDomain', []));
                    self::fail('expected a failure');
                } catch (OpenProviderException $e) {
                    self::assertNoSecret($e->getMessage());
                    self::assertNoSecret($e->getTraceAsString());
                    self::assertNoSecret(print_r($e->getTrace(), true));
                    self::assertNoSecret((string) $e);
                    self::assertNoSecret(var_export($e, true));
                }
            }
        } finally {
            ini_set('zend.exception_ignore_args', (string) $previous);
        }
    }

    private static function assertNoSecret(string $text): void
    {
        self::assertStringNotContainsString(self::PASSWORD, $text);
        self::assertStringNotContainsString(self::TOKEN, $text);
        self::assertStringNotContainsString(substr(self::PASSWORD, 0, 12), $text);
        self::assertStringNotContainsString(substr(self::TOKEN, 0, 12), $text);
    }

    private static function dump(mixed $value): string
    {
        ob_start();
        var_dump($value);

        return (string) ob_get_clean();
    }
}
