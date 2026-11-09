<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * JSON that survives decode → edit → encode without changing shape.
 *
 * Objects decode to PHP arrays so they are easy to edit. The two shapes a PHP array cannot
 * encode back as an object (an empty object, and an object whose keys are 0..n-1) stay
 * `stdClass`, so `{}` never turns into `[]` (Swagger's `"security": []` and `{}` mean
 * different things).
 */
final class Json
{
    private const ENCODE_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR;

    public static function decode(string $json, string $origin = 'JSON'): mixed
    {
        try {
            $value = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new SpecException(sprintf('%s is not valid JSON: %s', $origin, $e->getMessage()), 0, $e);
        }

        return self::fromDecoded($value);
    }

    /**
     * Pretty-printed, unescaped, newline-terminated: the same value always yields the same bytes.
     */
    public static function encode(mixed $value): string
    {
        return json_encode($value, self::ENCODE_FLAGS) . "\n";
    }

    /**
     * @return array<array-key, mixed>
     */
    public static function object(mixed $value, string $context): array
    {
        if ($value instanceof \stdClass) {
            return get_object_vars($value);
        }
        if (is_array($value) && ($value === [] || !array_is_list($value))) {
            return $value;
        }

        throw new SpecException(sprintf('%s must be a JSON object, got %s', $context, get_debug_type($value)));
    }

    /**
     * @return list<mixed>
     */
    public static function list(mixed $value, string $context): array
    {
        if (is_array($value) && array_is_list($value)) {
            return $value;
        }

        throw new SpecException(sprintf('%s must be a JSON array, got %s', $context, get_debug_type($value)));
    }

    public static function string(mixed $value, string $context): string
    {
        if (is_string($value)) {
            return $value;
        }

        throw new SpecException(sprintf('%s must be a string, got %s', $context, get_debug_type($value)));
    }

    private static function fromDecoded(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(self::fromDecoded(...), $value);
        }
        if (!$value instanceof \stdClass) {
            return $value;
        }

        $properties = [];
        foreach (get_object_vars($value) as $name => $property) {
            $properties[$name] = self::fromDecoded($property);
        }
        if ($properties === [] || array_keys($properties) === range(0, count($properties) - 1)) {
            return (object) $properties;
        }

        return $properties;
    }
}
