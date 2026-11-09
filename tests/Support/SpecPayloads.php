<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Support;

use Random\Randomizer;

/**
 * Payloads built from the spec's schemas, for the model round-trip property test.
 *
 * Besides values of the declared types, payloads carry what real responses can carry: explicit
 * nulls, values of another type than the spec declares and keys the spec does not declare.
 * `fits()` is the oracle for "the declared type", written from the spec independently of the
 * runtime's Fields.
 */
final class SpecPayloads
{
    private const MAX_DEPTH = 2;
    private const STRINGS = ['', '0', 'abc', 'ção 日本', ' spaced ', 'a/b?c=d&e', 'null', '123', 'true'];
    private const INTS = [0, -1, 1, PHP_INT_MAX, PHP_INT_MIN];
    private const NUMBERS = [0, 0.0, -1.5, 1.0E-7, 42, 3.14159];
    private const MISFITS = ['x', 7, 2.5, true, false, 0, [1, 'a'], ['k' => 'v'], [[]], ''];
    private const UNKNOWN_VALUES = [null, 0, false, '', 'text', [], [1, 2], ['a' => ['b' => 0.5]]];

    /**
     * @param array<string, array<string, mixed>> $definitions
     */
    public function __construct(
        private readonly array $definitions,
        private readonly Randomizer $random,
    ) {
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function definitions(string $specFile): array
    {
        $spec = json_decode((string) file_get_contents($specFile), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($spec) || !is_array($spec['definitions'] ?? null)) {
            throw new \UnexpectedValueException($specFile . ' has no definitions');
        }
        $definitions = [];
        foreach ($spec['definitions'] as $name => $definition) {
            $definitions[(string) $name] = self::map($definition);
        }

        return $definitions;
    }

    /**
     * @param array<string, array<string, mixed>> $definitions
     *
     * @return array<string, array<string, mixed>> property key => schema
     */
    public static function properties(array $definitions, string $definition): array
    {
        $properties = [];
        foreach (self::map($definitions[$definition]['properties'] ?? []) as $key => $schema) {
            $properties[(string) $key] = self::map($schema);
        }

        return $properties;
    }

    /**
     * Whether $value has the type the schema declares (nested objects are not inspected: a model
     * keeps whatever its own fields cannot type in its own $extra).
     *
     * @param array<string, array<string, mixed>> $definitions
     * @param array<string, mixed>                $schema
     */
    public static function fits(array $definitions, array $schema, mixed $value): bool
    {
        $target = self::target($definitions, $schema);
        if ($target !== null) {
            return isset($target['enum']) ? is_string($value) : is_array($value) && ($value === [] || !array_is_list($value));
        }

        return match ($schema['type'] ?? null) {
            'string' => is_string($value),
            'integer' => is_int($value),
            'number' => is_int($value) || (is_float($value) && is_finite($value)),
            'boolean' => is_bool($value),
            'array' => is_array($value) && array_is_list($value)
                && self::all($value, static fn (mixed $item): bool => self::fits($definitions, self::map($schema['items'] ?? null), $item)),
            'object' => is_array($value)
                && self::all($value, static fn (mixed $item): bool => self::fits($definitions, self::map($schema['additionalProperties'] ?? null), $item)),
            default => throw new \UnexpectedValueException('Unexpected schema ' . json_encode($schema)),
        };
    }

    /**
     * The deterministic cases every model must survive besides the random ones.
     *
     * @param array<string, array<string, mixed>> $definitions
     *
     * @return array<string, array<array-key, mixed>>
     */
    public static function edgeCases(array $definitions, string $definition): array
    {
        $zeros = [];
        $floatZeros = [];
        $nulls = [];
        $misfits = [];
        foreach (self::properties($definitions, $definition) as $key => $schema) {
            $zeros[$key] = self::zero($definitions, $schema);
            $floatZeros[$key] = ($schema['type'] ?? null) === 'number' ? 0.0 : $zeros[$key];
            $nulls[$key] = null;
            foreach (self::MISFITS as $candidate) {
                if (!self::fits($definitions, $schema, $candidate)) {
                    $misfits[$key] = $candidate;

                    break;
                }
            }
        }
        $unknown = ['__unknown__' => ['nested' => [0, false, '', [], null, 0.0]]];

        return [
            'empty' => [],
            'zero values' => $zeros,
            'float zeros' => $floatZeros,
            'explicit nulls' => $nulls,
            'values of another type' => $misfits,
            'unknown field' => $unknown,
            'zero values and an unknown field' => $zeros + $unknown,
            'numeric unknown key' => [7 => 'x'],
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    public function object(string $definition, int $depth = 0): array
    {
        $presence = [0, 30, 70, 100][$this->random->getInt(0, 3)];
        $payload = [];
        foreach (self::properties($this->definitions, $definition) as $key => $schema) {
            if ($this->random->getInt(1, 100) > $presence) {
                continue;
            }
            $roll = $this->random->getInt(0, 99);
            $payload[$key] = match (true) {
                $roll < 6 => null,
                $roll < 14 => $this->misfit($schema),
                default => $this->value($schema, $depth),
            };
        }
        if ($this->random->getInt(0, 9) < 3) {
            for ($i = $this->random->getInt(1, 2); $i > 0; --$i) {
                $key = $this->random->getInt(0, 3) === 0 ? $this->random->getInt(1000, 9999) : 'unknown_' . $this->random->getInt(0, 99);
                $payload[$key] = self::UNKNOWN_VALUES[$this->random->getInt(0, count(self::UNKNOWN_VALUES) - 1)];
            }
        }

        $shuffled = [];
        foreach ($this->random->shuffleArray(array_keys($payload)) as $key) {
            $shuffled[$key] = $payload[$key];
        }

        return $shuffled;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function value(array $schema, int $depth): mixed
    {
        $target = self::target($this->definitions, $schema);
        if ($target !== null) {
            if (isset($target['enum'])) {
                $values = array_values(array_filter(self::map($target['enum']), is_string(...)));

                return $this->random->getInt(0, 4) === 0 ? 'NEW_VALUE_' . $this->random->getInt(0, 9) : $this->pick($values);
            }

            return $depth >= self::MAX_DEPTH ? [] : $this->object(self::refName($schema), $depth + 1);
        }

        $count = $this->random->getInt(0, $depth === 0 ? 3 : 2);

        return match ($schema['type'] ?? null) {
            'string' => $this->random->getInt(0, 2) === 0 ? bin2hex($this->random->getBytes(4)) : $this->pick(self::STRINGS),
            'integer' => $this->random->getInt(0, 2) === 0 ? $this->random->getInt(-100000, 100000) : $this->pick(self::INTS),
            'number' => $this->random->getInt(0, 2) === 0 ? $this->random->getInt(-1000000, 1000000) / 1000.0 : $this->pick(self::NUMBERS),
            'boolean' => $this->random->getInt(0, 1) === 1,
            'array' => array_map(fn (): mixed => $this->value(self::map($schema['items'] ?? null), $depth), array_fill(0, $count, null)),
            'object' => $this->mapOf(self::map($schema['additionalProperties'] ?? null), $depth, $count),
            default => throw new \UnexpectedValueException('Unexpected schema ' . json_encode($schema)),
        };
    }

    /**
     * @param array<string, mixed> $schema
     *
     * @return array<array-key, mixed>
     */
    private function mapOf(array $schema, int $depth, int $count): array
    {
        $map = [];
        for ($i = 0; $i < $count; ++$i) {
            $map[$this->random->getInt(0, 3) === 0 ? $this->random->getInt(0, 50) : 'key_' . $this->random->getInt(0, 50)] = $this->value($schema, $depth);
        }

        return $map;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function misfit(array $schema): mixed
    {
        foreach ($this->random->shuffleArray(self::MISFITS) as $candidate) {
            if (!self::fits($this->definitions, $schema, $candidate)) {
                return $candidate;
            }
        }

        throw new \UnexpectedValueException('Every candidate fits ' . json_encode($schema));
    }

    /**
     * @param list<mixed> $values
     */
    private function pick(array $values): mixed
    {
        return $values[$this->random->getInt(0, count($values) - 1)];
    }

    /**
     * @param array<string, array<string, mixed>> $definitions
     * @param array<string, mixed>                $schema
     */
    private static function zero(array $definitions, array $schema): mixed
    {
        $target = self::target($definitions, $schema);
        if ($target !== null) {
            return isset($target['enum']) ? '' : [];
        }

        return match ($schema['type'] ?? null) {
            'string' => '',
            'integer', 'number' => 0,
            'boolean' => false,
            default => [],
        };
    }

    /**
     * @param array<string, array<string, mixed>> $definitions
     * @param array<string, mixed>                $schema
     *
     * @return array<string, mixed>|null the referenced definition
     */
    private static function target(array $definitions, array $schema): ?array
    {
        return isset($schema['$ref']) ? $definitions[self::refName($schema)] : null;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private static function refName(array $schema): string
    {
        $ref = $schema['$ref'] ?? null;
        if (!is_string($ref) || !str_starts_with($ref, '#/definitions/')) {
            throw new \UnexpectedValueException('Unexpected $ref ' . json_encode($ref));
        }

        return substr($ref, strlen('#/definitions/'));
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private static function all(array $values, \Closure $test): bool
    {
        foreach ($values as $value) {
            if ($test($value) !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    private static function map(mixed $value): array
    {
        if (!is_array($value)) {
            throw new \UnexpectedValueException('Expected a JSON object, got ' . get_debug_type($value));
        }
        $map = [];
        foreach ($value as $key => $item) {
            $map[(string) $key] = $item;
        }

        return $map;
    }
}
