<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Auth;

use Ashio\OpenProvider\Support\Clock;
use Ashio\OpenProvider\Support\SystemClock;
use Psr\SimpleCache\CacheInterface;

/**
 * Shares tokens between processes through any PSR-16 cache (requires psr/simple-cache).
 * The cache holds the raw token: protect it like the password. The cache's own exceptions (a
 * cache server that is down) reach the caller unchanged.
 */
final class Psr16TokenStore implements TokenStore
{
    public const PREFIX = 'openprovider.token.';

    /** The longest key PSR-16 guarantees every cache accepts. */
    private const KEY_LENGTH = 64;

    private readonly Clock $clock;

    public function __construct(private readonly CacheInterface $cache, ?Clock $clock = null)
    {
        $this->clock = $clock ?? new SystemClock();
    }

    /**
     * The cache key for a store key: the prefix and a hash, 64 characters from the set every
     * PSR-16 cache accepts (A-Z, a-z, 0-9, _ and .), whatever the store key holds.
     */
    public static function cacheKey(string $key): string
    {
        return self::PREFIX . substr(hash('sha256', $key), 0, self::KEY_LENGTH - strlen(self::PREFIX));
    }

    #[\Override]
    public function get(string $key): ?AccessToken
    {
        return $this->read($key, $this->cache->get(self::cacheKey($key)));
    }

    #[\Override]
    public function save(string $key, AccessToken $token): void
    {
        $ttl = $token->expiresAt->getTimestamp() - $this->clock->now()->getTimestamp();
        if ($ttl <= 0) {
            return;
        }
        $this->cache->set(self::cacheKey($key), [
            'token' => $token->token->reveal(),
            'reseller_id' => $token->resellerId,
            'expires_at' => $token->expiresAt->format(\DateTimeInterface::ATOM),
        ], $ttl);
    }

    #[\Override]
    public function delete(string $key): void
    {
        $this->cache->delete(self::cacheKey($key));
    }

    /**
     * A cache miss is no token; an entry this store did not write is no token either, and is
     * removed so the next login replaces it.
     */
    private function read(string $key, mixed $entry): ?AccessToken
    {
        if ($entry === null) {
            return null;
        }
        $token = is_array($entry) ? self::fromEntry($entry) : null;
        if ($token === null) {
            $this->cache->delete(self::cacheKey($key));
        }

        return $token;
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
