<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Spec;

use Ashio\OpenProvider\Auth\Authenticator;
use Ashio\OpenProvider\Client\ApiClient;
use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Client\Environment;
use Ashio\OpenProvider\Tools\Spec\Json;
use PHPUnit\Framework\TestCase;

/**
 * The core's constants match spec/behavior.json, so a sourced fact cannot drift from the code.
 */
final class BehaviorTest extends TestCase
{
    /** @var array<array-key, mixed> */
    private array $behavior;

    protected function setUp(): void
    {
        $this->behavior = Json::object(Json::decode((string) file_get_contents(__DIR__ . '/../../spec/behavior.json')), 'behavior');
    }

    public function testEnvironmentsAndBasePath(): void
    {
        $environments = $this->section('environments');
        self::assertSame(Json::object($environments['production'], 'production')['baseUri'], Environment::Production->baseUri());
        self::assertSame(Json::object($environments['sandbox'], 'sandbox')['baseUri'], Environment::Sandbox->baseUri());
        self::assertSame($this->section('basePath')['current'], ClientConfig::DEFAULT_BASE_PATH);
    }

    public function testTokenLifetimeAndExpiry(): void
    {
        $authentication = $this->section('authentication');

        self::assertSame($authentication['tokenTtlSeconds'], Authenticator::TOKEN_TTL_SECONDS);
        self::assertSame($authentication['refreshAfterSeconds'], Authenticator::REFRESH_AFTER_SECONDS);
        self::assertLessThan(Authenticator::TOKEN_TTL_SECONDS, Authenticator::REFRESH_AFTER_SECONDS);
        self::assertSame($authentication['expiredTokenCode'], ApiClient::EXPIRED_TOKEN_CODE);
    }

    /**
     * @return array<array-key, mixed>
     */
    private function section(string $name): array
    {
        return Json::object($this->behavior[$name], $name);
    }
}
