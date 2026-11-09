<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Client;

use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Client\Environment;
use Ashio\OpenProvider\Client\RetryPolicy;
use Ashio\OpenProvider\Exception\InvalidArgumentException;
use Ashio\OpenProvider\Sdk;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ClientConfigTest extends TestCase
{
    public function testEnvironmentsPointAtTheCurrentHosts(): void
    {
        self::assertSame('https://api.openprovider.eu', Environment::Production->baseUri());
        self::assertSame('https://api.sandbox.openprovider.nl', Environment::Sandbox->baseUri());
    }

    public function testAnEnvironmentNameThatIsNotKnownIsAnErrorInsteadOfProduction(): void
    {
        self::assertSame(Environment::Sandbox, Environment::from('sandbox'));

        $this->expectException(\ValueError::class);
        Environment::from('Sandbox ');
    }

    public function testDefaultsTalkToV1WithTheSdkUserAgent(): void
    {
        $config = ClientConfig::sandbox();

        self::assertSame(Environment::Sandbox, $config->environment);
        self::assertSame('/v1', $config->basePath);
        self::assertSame('https://api.sandbox.openprovider.nl/v1', $config->baseUrl());
        self::assertSame('ashio-openprovider-php/' . Sdk::VERSION, $config->userAgent);
        self::assertSame(60.0, $config->timeout);
        self::assertNull($config->ip);
        self::assertSame(0, $config->getRetry->maxRetries);
        self::assertSame('https://api.openprovider.eu/v1', ClientConfig::production()->baseUrl());
    }

    public function testTheBasePathCanStayOnV1betaDuringTheTransition(): void
    {
        self::assertSame(
            'https://api.openprovider.eu/v1beta',
            (new ClientConfig(Environment::Production, basePath: '/v1beta'))->baseUrl(),
        );
    }

    /**
     * @return iterable<string, array{\Closure(): ClientConfig}>
     */
    public static function invalidSettings(): iterable
    {
        yield 'base path without slash' => [static fn () => new ClientConfig(Environment::Sandbox, basePath: 'v1')];
        yield 'base path with trailing slash' => [static fn () => new ClientConfig(Environment::Sandbox, basePath: '/v1/')];
        yield 'base path with a query' => [static fn () => new ClientConfig(Environment::Sandbox, basePath: '/v1?x=1')];
        yield 'zero timeout' => [static fn () => new ClientConfig(Environment::Sandbox, timeout: 0.0)];
        yield 'negative connect timeout' => [static fn () => new ClientConfig(Environment::Sandbox, connectTimeout: -1.0)];
        yield 'user agent with a newline' => [static fn () => new ClientConfig(Environment::Sandbox, userAgent: "sdk\r\nX-Injected: 1")];
        yield 'empty user agent' => [static fn () => new ClientConfig(Environment::Sandbox, userAgent: ' ')];
        yield 'ip that is not an address' => [static fn () => new ClientConfig(Environment::Sandbox, ip: 'localhost')];
    }

    /**
     * @param \Closure(): ClientConfig $build
     */
    #[DataProvider('invalidSettings')]
    public function testRejectsInvalidSettings(\Closure $build): void
    {
        $this->expectException(InvalidArgumentException::class);

        $build();
    }

    public function testAcceptsAnIpAndARetryPolicyForReads(): void
    {
        $config = new ClientConfig(Environment::Sandbox, ip: '0.0.0.0', getRetry: new RetryPolicy(2));

        self::assertSame('0.0.0.0', $config->ip);
        self::assertSame(2, $config->getRetry->maxRetries);
    }

    public function testRetryDelaysRepeatTheLastStepAndRejectNegativeCounts(): void
    {
        $policy = new RetryPolicy(5, [100, 200]);

        self::assertSame([100, 200, 200, 200, 200], array_map($policy->delayMs(...), [1, 2, 3, 4, 5]));

        $this->expectException(InvalidArgumentException::class);
        new RetryPolicy(-1);
    }
}
