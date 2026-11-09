<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Conformance;

use Ashio\OpenProvider\Tools\Generate\ModelSet;
use Ashio\OpenProvider\Tools\Spec\Json;

/**
 * Values for probing operations, taken from the spec: a definition's own `example` when it has
 * one, otherwise a value of the declared type built from its properties. Deterministic, so a
 * failing probe fails the same way every run.
 */
final class SpecSamples
{
    private const MAX_DEPTH = 3;

    /**
     * @param array<array-key, mixed> $definitions
     */
    public function __construct(private readonly array $definitions)
    {
    }

    /**
     * The example of an object definition, or one built from its properties.
     *
     * @return array<array-key, mixed>
     */
    public function object(string $definition, int $depth = 0): array
    {
        $schema = Json::object($this->definitions[$definition] ?? null, $definition);
        if (isset($schema['example']) && is_array($schema['example'])) {
            return $schema['example'];
        }

        return $this->built($schema, $depth);
    }

    /**
     * A value of the declared type for every property of an object definition, ignoring its
     * example: what a model must be able to hold in typed properties.
     *
     * @return array<array-key, mixed>
     */
    public function typed(string $definition): array
    {
        return $this->built(Json::object($this->definitions[$definition] ?? null, $definition), 0);
    }

    /**
     * What a success envelope's `data` holds for this envelope definition: the envelope's own
     * example when it has `data`, otherwise a sample of the data schema.
     */
    public function data(string $envelope): mixed
    {
        $schema = Json::object($this->definitions[$envelope] ?? null, $envelope);
        if (isset($schema['example']) && is_array($schema['example']) && array_key_exists('data', $schema['example'])) {
            return $schema['example']['data'];
        }
        $properties = Json::object($schema['properties'] ?? [], "$envelope properties");

        return $this->value(Json::object($properties['data'] ?? [], "$envelope data"), 0);
    }

    /**
     * A value for a path or query parameter. $seed varies the value between the parameters of
     * one call, so a value that lands under the wrong name is caught; booleans alternate and
     * start at false, which is the value a filter that drops falsy values loses.
     *
     * @param array<array-key, mixed> $parameter a Swagger 2.0 parameter object
     */
    public static function parameter(array $parameter, int $seed): mixed
    {
        $type = Json::optionalString($parameter, 'type');
        if ($type === 'array') {
            $items = isset($parameter['items']) && is_array($parameter['items']) ? $parameter['items'] : [];

            return [self::parameter($items, $seed), self::parameter($items, $seed + 1)];
        }
        if (isset($parameter['enum']) && is_array($parameter['enum']) && $parameter['enum'] !== []) {
            return array_values($parameter['enum'])[$seed % count($parameter['enum'])];
        }

        return match ($type) {
            'integer' => 40 + $seed,
            'number' => 1.5 + (float) $seed,
            'boolean' => $seed % 2 === 1,
            default => 'v' . $seed . ' ä/&=',
        };
    }

    /**
     * @param array<array-key, mixed> $schema
     */
    private function value(array $schema, int $depth): mixed
    {
        if (isset($schema['$ref'])) {
            $name = ModelSet::refName($schema['$ref']);
            $target = Json::object($this->definitions[$name] ?? null, $name);
            if (($target['type'] ?? null) !== 'object') {
                return $this->value($target, $depth);
            }

            return $depth >= self::MAX_DEPTH ? [] : $this->object($name, $depth + 1);
        }
        if (array_key_exists('example', $schema)) {
            return $schema['example'];
        }
        if (isset($schema['enum']) && is_array($schema['enum']) && $schema['enum'] !== []) {
            return array_values($schema['enum'])[0];
        }

        return match ($schema['type'] ?? null) {
            'integer' => 7,
            'number' => 2.5,
            'boolean' => true,
            'string' => 'sample',
            'array' => $depth >= self::MAX_DEPTH ? [] : [$this->value(Json::object($schema['items'] ?? [], 'items'), $depth + 1)],
            'object' => isset($schema['properties']) ? $this->built($schema, $depth) : $this->map($schema, $depth),
            default => 'sample',
        };
    }

    /**
     * @param array<array-key, mixed> $schema
     *
     * @return array<array-key, mixed>
     */
    private function built(array $schema, int $depth): array
    {
        return array_map(
            fn (array $property): mixed => $this->value($property, $depth),
            Json::objectMap($schema['properties'] ?? [], 'properties'),
        );
    }

    /**
     * @param array<array-key, mixed> $schema
     *
     * @return array<array-key, mixed>
     */
    private function map(array $schema, int $depth): array
    {
        $values = $schema['additionalProperties'] ?? null;
        if (!is_array($values) || $depth >= self::MAX_DEPTH) {
            return [];
        }

        return ['key' => $this->value($values, $depth + 1)];
    }
}
