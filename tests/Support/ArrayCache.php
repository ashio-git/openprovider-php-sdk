<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Support;

use Psr\SimpleCache\CacheInterface;

/**
 * Minimal PSR-16 cache that records the TTL of each write. It is strict about keys: it refuses
 * any key outside what PSR-16 guarantees every cache accepts (up to 64 characters of A-Z, a-z,
 * 0-9, _ and .), as some implementations do.
 */
final class ArrayCache implements CacheInterface
{
    /** @var array<string, mixed> */
    public array $values = [];

    /** @var array<string, \DateInterval|int|null> */
    public array $ttls = [];

    public function get(string $key, mixed $default = null): mixed
    {
        self::guaranteed($key);

        return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        self::guaranteed($key);
        $this->values[$key] = $value;
        $this->ttls[$key] = $ttl;

        return true;
    }

    public function delete(string $key): bool
    {
        self::guaranteed($key);
        unset($this->values[$key], $this->ttls[$key]);

        return true;
    }

    public function clear(): bool
    {
        $this->values = [];
        $this->ttls = [];

        return true;
    }

    /**
     * @param iterable<string> $keys
     *
     * @return iterable<string, mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $this->get($key, $default);
        }

        return $out;
    }

    /**
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /**
     * @param iterable<string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function has(string $key): bool
    {
        self::guaranteed($key);

        return array_key_exists($key, $this->values);
    }

    private static function guaranteed(string $key): void
    {
        if (preg_match('/^[A-Za-z0-9_.]{1,64}$/', $key) !== 1) {
            throw new \LogicException(sprintf('"%s" (%d characters) is not a key every PSR-16 cache accepts', $key, strlen($key)));
        }
    }
}
