<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Serialization;

/**
 * The reading and writing rules of the generated models.
 *
 * Reading: a reader takes `$key` out of `$rest` and returns its value when the value has the
 * declared type. Otherwise it returns null and leaves the key in `$rest` as received: absent, an
 * explicit null, or a value of another type than the spec declares. What is left of `$rest` is
 * the model's `$extra`, so nothing the API sent is lost.
 *
 * Writing: typed values that are null are left out (absent), 0, false, '' and empty lists are
 * kept, and `$extra` follows.
 *
 * @internal called by the classes `php tools/generate models` writes into src/Model
 */
final class Fields
{
    /**
     * @param array<array-key, mixed> $rest
     *
     * @param-out array<array-key, mixed> $rest
     */
    public static function string(array &$rest, string $key): ?string
    {
        return self::take($rest, $key, self::asString(...));
    }

    /**
     * @param array<array-key, mixed> $rest
     *
     * @param-out array<array-key, mixed> $rest
     */
    public static function int(array &$rest, string $key): ?int
    {
        return self::take($rest, $key, self::asInt(...));
    }

    /**
     * A JSON number: an integer stays int and a fraction stays float, so the value round-trips.
     *
     * @param array<array-key, mixed> $rest
     *
     * @param-out array<array-key, mixed> $rest
     */
    public static function number(array &$rest, string $key): int|float|null
    {
        return self::take($rest, $key, self::asNumber(...));
    }

    /**
     * @param array<array-key, mixed> $rest
     *
     * @param-out array<array-key, mixed> $rest
     */
    public static function bool(array &$rest, string $key): ?bool
    {
        return self::take($rest, $key, self::asBool(...));
    }

    /**
     * @template T of Model
     *
     * @param array<array-key, mixed> $rest
     * @param class-string<T>         $class
     *
     * @param-out array<array-key, mixed> $rest
     *
     * @return T|null
     */
    public static function object(array &$rest, string $key, string $class): ?Model
    {
        return self::take($rest, $key, static fn (mixed $value): ?Model => self::asObject($value, $class));
    }

    /**
     * @param array<array-key, mixed> $rest
     *
     * @param-out array<array-key, mixed> $rest
     *
     * @return list<string>|null
     */
    public static function strings(array &$rest, string $key): ?array
    {
        return self::take($rest, $key, static fn (mixed $value): ?array => self::listOf($value, self::asString(...)));
    }

    /**
     * @param array<array-key, mixed> $rest
     *
     * @param-out array<array-key, mixed> $rest
     *
     * @return list<int>|null
     */
    public static function ints(array &$rest, string $key): ?array
    {
        return self::take($rest, $key, static fn (mixed $value): ?array => self::listOf($value, self::asInt(...)));
    }

    /**
     * @param array<array-key, mixed> $rest
     *
     * @param-out array<array-key, mixed> $rest
     *
     * @return list<int|float>|null
     */
    public static function numbers(array &$rest, string $key): ?array
    {
        return self::take($rest, $key, static fn (mixed $value): ?array => self::listOf($value, self::asNumber(...)));
    }

    /**
     * @param array<array-key, mixed> $rest
     *
     * @param-out array<array-key, mixed> $rest
     *
     * @return list<bool>|null
     */
    public static function bools(array &$rest, string $key): ?array
    {
        return self::take($rest, $key, static fn (mixed $value): ?array => self::listOf($value, self::asBool(...)));
    }

    /**
     * @template T of Model
     *
     * @param array<array-key, mixed> $rest
     * @param class-string<T>         $class
     *
     * @param-out array<array-key, mixed> $rest
     *
     * @return list<T>|null
     */
    public static function objects(array &$rest, string $key, string $class): ?array
    {
        return self::take($rest, $key, static fn (mixed $value): ?array => self::listOf($value, static fn (mixed $item): ?Model => self::asObject($item, $class)));
    }

    /**
     * @param array<array-key, mixed> $rest
     *
     * @param-out array<array-key, mixed> $rest
     *
     * @return array<array-key, string>|null
     */
    public static function stringMap(array &$rest, string $key): ?array
    {
        return self::take($rest, $key, static fn (mixed $value): ?array => self::mapOf($value, self::asString(...)));
    }

    /**
     * @param array<array-key, mixed> $rest
     *
     * @param-out array<array-key, mixed> $rest
     *
     * @return array<array-key, int>|null
     */
    public static function intMap(array &$rest, string $key): ?array
    {
        return self::take($rest, $key, static fn (mixed $value): ?array => self::mapOf($value, self::asInt(...)));
    }

    /**
     * @param array<array-key, mixed> $rest
     *
     * @param-out array<array-key, mixed> $rest
     *
     * @return array<array-key, int|float>|null
     */
    public static function numberMap(array &$rest, string $key): ?array
    {
        return self::take($rest, $key, static fn (mixed $value): ?array => self::mapOf($value, self::asNumber(...)));
    }

    /**
     * @param array<array-key, mixed> $rest
     *
     * @param-out array<array-key, mixed> $rest
     *
     * @return array<array-key, bool>|null
     */
    public static function boolMap(array &$rest, string $key): ?array
    {
        return self::take($rest, $key, static fn (mixed $value): ?array => self::mapOf($value, self::asBool(...)));
    }

    /**
     * @template T of Model
     *
     * @param array<array-key, mixed> $rest
     * @param class-string<T>         $class
     *
     * @param-out array<array-key, mixed> $rest
     *
     * @return array<array-key, T>|null
     */
    public static function objectMap(array &$rest, string $key, string $class): ?array
    {
        return self::take($rest, $key, static fn (mixed $value): ?array => self::mapOf($value, static fn (mixed $item): ?Model => self::asObject($item, $class)));
    }

    /**
     * The API representation of a model: typed values that are not null, nested models as
     * arrays, then `$extra` (a typed value wins over an `$extra` entry with the same key).
     *
     * @param array<string, mixed>    $fields API key => typed value, null meaning absent
     * @param array<array-key, mixed> $extra
     *
     * @return array<array-key, mixed>
     */
    public static function toArray(array $fields, array $extra): array
    {
        return array_map(self::plain(...), self::present($fields)) + $extra;
    }

    /**
     * The JSON form of a model: always an object, with nested models left for json_encode() to
     * serialize (so an empty one is `{}`, not `[]`) and the `$maps` fields cast to objects.
     *
     * @param array<string, mixed>    $fields API key => typed value, null meaning absent
     * @param array<array-key, mixed> $extra
     * @param list<string>            $maps   keys whose value is a JSON object (a map), even when empty
     */
    public static function toJson(array $fields, array $extra, array $maps = []): \stdClass
    {
        $present = self::present($fields);
        $objects = array_map(
            static fn (mixed $value): mixed => is_array($value) ? self::jsonObject($value) : $value,
            array_intersect_key($present, array_flip($maps)),
        );

        return self::jsonObject(array_replace($present, $objects) + $extra);
    }

    /**
     * An array as a JSON object, whatever its keys: `[]` becomes `{}` and a list `{"0": …}`.
     *
     * @param array<array-key, mixed> $map
     */
    public static function jsonObject(array $map): \stdClass
    {
        $object = new \stdClass();
        foreach (array_keys($map) as $key) {
            $object->{(string) $key} = $map[$key];
        }

        return $object;
    }

    /**
     * The fields that are set, in their order: null means absent.
     *
     * @param array<string, mixed> $fields
     *
     * @return array<string, mixed>
     */
    private static function present(array $fields): array
    {
        return array_filter($fields, static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @template T
     *
     * @param array<array-key, mixed> $rest
     * @param \Closure(mixed): (T|null) $convert null when the value does not have the type
     *
     * @param-out array<array-key, mixed> $rest
     *
     * @return T|null
     */
    private static function take(array &$rest, string $key, \Closure $convert): mixed
    {
        if (!isset($rest[$key])) {
            return null;
        }
        $value = $convert($rest[$key]);
        if ($value !== null) {
            unset($rest[$key]);
        }

        return $value;
    }

    /**
     * @template T
     *
     * @param \Closure(mixed): (T|null) $item
     *
     * @return list<T>|null
     */
    private static function listOf(mixed $value, \Closure $item): ?array
    {
        if (!is_array($value) || !array_is_list($value)) {
            return null;
        }
        $items = self::each($value, $item);

        return $items === null ? null : array_values($items);
    }

    /**
     * Any array: a JSON object whose keys are 0..n-1 decodes to a PHP list.
     *
     * @template T
     *
     * @param \Closure(mixed): (T|null) $item
     *
     * @return array<array-key, T>|null
     */
    private static function mapOf(mixed $value, \Closure $item): ?array
    {
        return is_array($value) ? self::each($value, $item) : null;
    }

    /**
     * @template K of array-key
     * @template T
     *
     * @param array<K, mixed>           $values
     * @param \Closure(mixed): (T|null) $item
     *
     * @return array<K, T>|null null as soon as one item does not have the type
     */
    private static function each(array $values, \Closure $item): ?array
    {
        $out = [];
        foreach (array_keys($values) as $key) {
            $converted = $item($values[$key]);
            if ($converted === null) {
                return null;
            }
            $out[$key] = $converted;
        }

        return $out;
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    private static function asObject(mixed $value, string $class): ?Model
    {
        return is_array($value) && ($value === [] || !array_is_list($value)) ? $class::fromArray($value) : null;
    }

    private static function asString(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    private static function asInt(mixed $value): ?int
    {
        return is_int($value) ? $value : null;
    }

    private static function asNumber(mixed $value): int|float|null
    {
        return is_int($value) || (is_float($value) && is_finite($value)) ? $value : null;
    }

    private static function asBool(mixed $value): ?bool
    {
        return is_bool($value) ? $value : null;
    }

    private static function plain(mixed $value): mixed
    {
        if ($value instanceof Model) {
            return $value->toArray();
        }

        return is_array($value) ? array_map(self::plain(...), $value) : $value;
    }
}
