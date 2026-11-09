<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Client;

use Ashio\OpenProvider\Exception\InvalidArgumentException;
use Ashio\OpenProvider\Sdk;

/**
 * Connection settings. No credentials live here, so the config is safe to log.
 */
final class ClientConfig
{
    public const DEFAULT_BASE_PATH = '/v1';

    public readonly string $userAgent;

    /**
     * @param string      $basePath       `/v1` (current); `/v1beta` is switched off on 2027-06-30
     * @param float       $timeout        seconds for a whole request
     * @param float       $connectTimeout seconds to connect
     * @param string|null $ip             sent with the login; null omits it
     * @param RetryPolicy $getRetry       retries for reads; none by default
     */
    public function __construct(
        public readonly Environment $environment,
        public readonly string $basePath = self::DEFAULT_BASE_PATH,
        public readonly float $timeout = 60.0,
        public readonly float $connectTimeout = 10.0,
        ?string $userAgent = null,
        public readonly ?string $ip = null,
        public readonly RetryPolicy $getRetry = new RetryPolicy(),
    ) {
        if (preg_match('#^(/[A-Za-z0-9._~-]+)+$#', $basePath) !== 1) {
            throw new InvalidArgumentException(sprintf('basePath must look like "/v1", got "%s"', $basePath));
        }
        if ($timeout <= 0.0 || $connectTimeout <= 0.0) {
            throw new InvalidArgumentException('timeout and connectTimeout must be positive');
        }
        $userAgent ??= Sdk::userAgent();
        if (trim($userAgent) === '' || preg_match('/[\x00-\x1F\x7F]/', $userAgent) === 1) {
            throw new InvalidArgumentException('userAgent must be a non-empty single line');
        }
        if ($ip !== null && filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new InvalidArgumentException(sprintf('ip must be an IPv4 or IPv6 address, got "%s"', $ip));
        }
        $this->userAgent = $userAgent;
    }

    public static function production(): self
    {
        return new self(Environment::Production);
    }

    public static function sandbox(): self
    {
        return new self(Environment::Sandbox);
    }

    /**
     * `https://host/v1`, without a trailing slash.
     */
    public function baseUrl(): string
    {
        return $this->environment->baseUri() . $this->basePath;
    }
}
