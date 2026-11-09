<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Auth;

use Ashio\OpenProvider\Support\Clock;
use Ashio\OpenProvider\Support\SystemClock;
use Psr\SimpleCache\CacheInterface;

/**
 * Shares tokens between processes through any PSR-16 cache (requires psr/simple-cache).
 * The cache holds the raw token: protect it like the password.
 */
final class Psr16TokenStore implements TokenStore
{
    public const PREFIX = 'openprovider.token.';

    private readonly Clock $clock;

    public function __construct(private readonly CacheInterface $cache, ?Clock $clock = null)
    {
        $this->clock = $clock ?? new SystemClock();
    }

    #[\Override]
    public function get(string $key): ?AccessToken
    {
        $entry = $this->cache->get(self::PREFIX . $key);
        if ($entry === null) {
            return null;
        }
        $token = is_array($entry) ? self::fromEntry($entry) : null;
        if ($token === null) {
            $this->cache->delete(self::PREFIX . $key);
        }

        return $token;
    }

    #[\Override]
    public function save(string $key, AccessToken $token): void
    {
        $ttl = $token->expiresAt->getTimestamp() - $this->clock->now()->getTimestamp();
        if ($ttl <= 0) {
            return;
        }
        $this->cache->set(self::PREFIX . $key, [
            'token' => $token->token->reveal(),
            'reseller_id' => $token->resellerId,
            'expires_at' => $token->expiresAt->format(\DateTimeInterface::ATOM),
        ], $ttl);
    }

    #[\Override]
    public function delete(string $key): void
    {
        $this->cache->delete(self::PREFIX . $key);
    }

    /**
     * @param array<array-key, mixed> $entry
     */
    private static function fromEntry(array $entry): ?AccessToken
    {
        $token = $entry['token'] ?? null;
        $resellerId = $entry['reseller_id'] ?? null;
        $expiresAt = $entry['expires_at'] ?? null;
        if (!is_string($token) || $token === '' || !is_string($expiresAt) || ($resellerId !== null && !is_int($resellerId))) {
            return null;
        }
        $expires = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $expiresAt);

        return $expires === false ? null : new AccessToken(new Secret($token), $resellerId, $expires);
    }
}
