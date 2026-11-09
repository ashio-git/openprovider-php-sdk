<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

use Ashio\OpenProvider\Serialization\Fields;

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

    /**
     * @return array<array-key, mixed>|\stdClass|scalar|null
     */
    public static function decode(string $json, string $origin = 'JSON'): array|\stdClass|bool|int|float|string|null
    {
        try {
            return self::fromDecoded(json_decode($json, false, 512, JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
            throw new SpecException(sprintf('%s is not valid JSON: %s', $origin, $e->getMessage()), 0, $e);
        }
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

    /**
     * A JSON object whose every value is an object (`paths`, `responses`, `properties`).
     *
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function objectMap(mixed $value, string $context): array
    {
        $map = self::object($value, $context);
        $out = [];
        foreach (array_keys($map) as $key) {
            $out[$key] = self::object($map[$key], $context . '.' . $key);
        }

        return $out;
    }

    /**
     * A JSON array of objects (`parameters`, `x-tagGroups`).
     *
     * @return list<array<array-key, mixed>>
     */
    public static function objectList(mixed $value, string $context): array
    {
        $list = self::list($value, $context);
        $out = [];
        foreach (array_keys($list) as $i) {
            $out[] = self::object($list[$i], sprintf('%s[%d]', $context, $i));
        }

        return $out;
    }

    /**
     * A JSON array of strings (`tags`).
     *
     * @return list<string>
     */
    public static function stringList(mixed $value, string $context): array
    {
        $list = self::list($value, $context);
        $out = [];
        foreach (array_keys($list) as $i) {
            $out[] = self::string($list[$i], sprintf('%s[%d]', $context, $i));
        }

        return $out;
    }

    /**
     * $value as what JSON can hold: null, a scalar, an array or an object (stdClass).
     *
     * @return array<array-key, mixed>|\stdClass|scalar|null
     */
    public static function value(mixed $value, string $context): array|\stdClass|bool|int|float|string|null
    {
        if ($value === null || is_scalar($value) || is_array($value) || $value instanceof \stdClass) {
            return $value;
        }

        throw new SpecException(sprintf('%s must be a JSON value, got %s', $context, get_debug_type($value)));
    }

    /**
     * $map with each value replaced by $convert(value, key); keys and order are kept.
     *
     * @template K of array-key
     *
     * @param array<K, mixed>           $map
     * @param \Closure(mixed, K): mixed $convert
     *
     * @return array<K, mixed>
     */
    public static function mapValues(array $map, \Closure $convert): array
    {
        $keys = array_keys($map);

        return array_combine($keys, array_map($convert, $map, $keys));
    }

    /**
     * The string at $key, or null when the key is absent or holds something else.
     *
     * @param array<array-key, mixed> $object
     */
    public static function optionalString(array $object, string $key): ?string
    {
        return isset($object[$key]) && is_string($object[$key]) ? $object[$key] : null;
    }

    public static function string(mixed $value, string $context): string
    {
        if (is_string($value)) {
            return $value;
        }

        throw new SpecException(sprintf('%s must be a string, got %s', $context, get_debug_type($value)));
    }

    /**
     * @return array<array-key, mixed>|\stdClass|scalar|null
     */
    private static function fromDecoded(mixed $value): array|\stdClass|bool|int|float|string|null
    {
        if (is_array($value)) {
            return array_map(self::fromDecoded(...), $value);
        }
        if (!$value instanceof \stdClass) {
            return self::value($value, 'JSON');
        }

        // Read as an array, a stdClass gives "0", "1", … back as integer keys: such an object is a list.
        $properties = array_map(self::fromDecoded(...), (array) $value);
        if ($properties === [] || array_is_list($properties)) {
            return Fields::jsonObject($properties);
        }

        return $properties;
    }
}
