<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Http;

use Ashio\OpenProvider\Exception\InvalidArgumentException;
use Ashio\OpenProvider\Spec\OperationSpec;
use Ashio\OpenProvider\Spec\ParameterLocation;
use Ashio\OpenProvider\Spec\ParameterSpec;

/**
 * Turns arguments into a request exactly as the spec declares each parameter:
 *
 * - the parameter's `in`, not the HTTP method, decides query or body (DELETE with a query works);
 * - names are sent as declared, dotted ones included (`domain.name=…`); unknown names are
 *   refused, so a camelCase filter cannot be silently ignored by the API;
 * - arrays follow `collectionFormat` (`multi` repeats the key); booleans are `true`/`false`;
 * - only null means "absent": 0, false, '' and empty lists are sent.
 */
final class RequestEncoder
{
    private const JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR;

    /**
     * @param array<array-key, mixed> $arguments keyed by the spec's parameter names
     */
    public function encode(OperationSpec $operation, array $arguments): EncodedRequest
    {
        foreach (array_keys($arguments) as $name) {
            if ($operation->parameter((string) $name) === null) {
                throw new InvalidArgumentException(sprintf(
                    '%s has no parameter "%s"; it accepts: %s',
                    $operation->operationId,
                    $name,
                    implode(', ', array_map(static fn (ParameterSpec $p): string => $p->name, $operation->parameters)) ?: 'none',
                ));
            }
        }

        $path = $operation->path;
        $query = [];
        $body = null;
        foreach ($operation->parameters as $parameter) {
            $value = $arguments[$parameter->name] ?? null;
            if ($value === null && $parameter->required && $parameter->in !== ParameterLocation::Body) {
                throw new InvalidArgumentException(sprintf('%s requires "%s"', $operation->operationId, $parameter->name));
            }
            switch ($parameter->in) {
                case ParameterLocation::Path:
                    $segment = self::scalar($parameter, (string) $parameter->type, $value);
                    if ($segment === '') {
                        throw new InvalidArgumentException(sprintf('%s: path parameter "%s" cannot be empty', $operation->operationId, $parameter->name));
                    }
                    $path = str_replace('{' . $parameter->name . '}', rawurlencode($segment), $path);

                    break;
                case ParameterLocation::Query:
                    if ($value !== null) {
                        array_push($query, ...self::queryPairs($parameter, $value));
                    }

                    break;
                case ParameterLocation::Body:
                    if ($value === null && $parameter->required) {
                        throw new InvalidArgumentException(sprintf('%s requires a "%s"', $operation->operationId, $parameter->name));
                    }
                    $body = self::json($parameter, $value ?? []);

                    break;
            }
        }

        return new EncodedRequest($operation->method, $path . ($query === [] ? '' : '?' . implode('&', $query)), $body);
    }

    /**
     * @return list<string> encoded name=value pairs
     */
    private static function queryPairs(ParameterSpec $parameter, mixed $value): array
    {
        $name = rawurlencode($parameter->name);
        if ($parameter->type !== 'array') {
            return [$name . '=' . rawurlencode(self::scalar($parameter, (string) $parameter->type, $value))];
        }
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException(sprintf('Parameter "%s" expects a list, got %s', $parameter->name, get_debug_type($value)));
        }
        $items = array_map(static fn (mixed $item): string => self::scalar($parameter, (string) $parameter->itemType, $item), $value);
        if ($items === []) {
            return [];
        }
        if ($parameter->collectionFormat === 'multi') {
            return array_map(static fn (string $item): string => $name . '=' . rawurlencode($item), $items);
        }

        return [$name . '=' . rawurlencode(implode(ParameterSpec::COLLECTION_FORMATS[$parameter->collectionFormat], $items))];
    }

    private static function scalar(ParameterSpec $parameter, string $type, mixed $value): string
    {
        $text = match ($type) {
            'integer' => is_int($value) ? (string) $value : null,
            'number' => is_int($value) || (is_float($value) && is_finite($value)) ? (string) $value : null,
            'boolean' => is_bool($value) ? ($value ? 'true' : 'false') : null,
            default => is_string($value) || is_int($value) || $value instanceof \Stringable
                ? (string) $value
                : (is_float($value) && is_finite($value) ? (string) $value : null),
        };
        if ($text === null) {
            throw new InvalidArgumentException(sprintf(
                'Parameter "%s" expects %s, got %s',
                $parameter->name,
                $type === 'string' ? 'a string' : 'a' . ($type === 'integer' ? 'n ' : ' ') . $type,
                get_debug_type($value),
            ));
        }

        return $text;
    }

    private static function json(ParameterSpec $parameter, mixed $value): string
    {
        if (!is_array($value) && !$value instanceof \JsonSerializable && !$value instanceof \stdClass) {
            throw new InvalidArgumentException(sprintf('The "%s" parameter must be an array or an object, got %s', $parameter->name, get_debug_type($value)));
        }
        $normalized = self::withoutNulls($value, $parameter->name);
        if ($normalized === []) {
            $normalized = new \stdClass();
        }

        try {
            return json_encode($normalized, self::JSON_FLAGS);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException(sprintf('The "%s" parameter cannot be encoded as JSON: %s', $parameter->name, $e->getMessage()), 0, $e);
        }
    }

    /**
     * Drops null entries of maps at every depth; lists keep their positions.
     */
    private static function withoutNulls(mixed $value, string $where): mixed
    {
        if ($value instanceof \JsonSerializable) {
            $value = $value->jsonSerialize();
        }
        if ($value instanceof \stdClass) {
            $map = self::mapWithoutNulls(get_object_vars($value), $where);

            return $map === [] ? new \stdClass() : (object) $map;
        }
        if (is_array($value)) {
            if (array_is_list($value)) {
                return array_map(static fn (mixed $item): mixed => self::withoutNulls($item, $where), $value);
            }
            $map = self::mapWithoutNulls($value, $where);

            return $map === [] ? new \stdClass() : $map;
        }
        if (is_float($value) && !is_finite($value)) {
            throw new InvalidArgumentException(sprintf('The "%s" parameter holds a float JSON cannot represent', $where));
        }
        if (is_object($value) || is_resource($value)) {
            throw new InvalidArgumentException(sprintf('The "%s" parameter holds a %s; pass arrays or JsonSerializable objects', $where, get_debug_type($value)));
        }

        return $value;
    }

    /**
     * @param array<array-key, mixed> $map
     *
     * @return array<array-key, mixed>
     */
    private static function mapWithoutNulls(array $map, string $where): array
    {
        $out = [];
        foreach ($map as $key => $item) {
            if ($item !== null) {
                $out[$key] = self::withoutNulls($item, $where);
            }
        }

        return $out;
    }
}
