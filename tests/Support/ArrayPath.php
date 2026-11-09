<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Support;

/**
 * Reads and writes deep inside decoded JSON without asserting every level's type by hand.
 */
final class ArrayPath
{
    /**
     * The value at $path, or null when a level is missing or not an array.
     */
    public static function at(mixed $value, string ...$path): mixed
    {
        foreach ($path as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * The array at $path, or [] when there is none.
     *
     * @return array<array-key, mixed>
     */
    public static function entries(mixed $value, string ...$path): array
    {
        $value = self::at($value, ...$path);

        return is_array($value) ? $value : [];
    }

    /**
     * A copy of $array with $value at $path, creating the missing levels.
     *
     * @param array<array-key, mixed> $array
     *
     * @return array<array-key, mixed>
     */
    public static function with(array $array, mixed $value, string $key, string ...$path): array
    {
        if ($path === []) {
            $array[$key] = $value;

            return $array;
        }
        $array[$key] = self::with(self::entries($array, $key), $value, ...$path);

        return $array;
    }
}
