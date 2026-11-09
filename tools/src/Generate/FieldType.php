<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Generate;

use Ashio\OpenProvider\Tools\Spec\Json;
use Ashio\OpenProvider\Tools\Spec\SpecException;

/**
 * The PHP type of one property and the Fields reader that fills it.
 *
 * Supported: string, integer, number, boolean, a $ref to an object or a string enum, a list of
 * any of those, and a map (`additionalProperties`) of any of those. Anything else is refused
 * with its location, so a spec change the generator cannot type stops generation instead of
 * yielding a wrong model.
 */
final class FieldType
{
    private const READERS = [
        'one' => ['string' => 'string', 'int' => 'int', 'number' => 'number', 'bool' => 'bool', 'object' => 'object'],
        'list' => ['string' => 'strings', 'int' => 'ints', 'number' => 'numbers', 'bool' => 'bools', 'object' => 'objects'],
        'map' => ['string' => 'stringMap', 'int' => 'intMap', 'number' => 'numberMap', 'bool' => 'boolMap', 'object' => 'objectMap'],
    ];
    private const SCALARS = ['string' => 'string', 'int' => 'int', 'number' => 'int|float', 'bool' => 'bool'];

    /**
     * @param 'string'|'int'|'number'|'bool'|'object' $kind
     * @param 'one'|'list'|'map'                      $container
     * @param string|null                             $class     the model class, for objects
     * @param string|null                             $enum      the enum class whose constants a string takes
     */
    private function __construct(
        public readonly string $kind,
        public readonly string $container,
        public readonly ?string $class = null,
        public readonly ?string $enum = null,
    ) {
    }

    /**
     * @param array<array-key, mixed> $schema
     * @param array<array-key, mixed> $definitions
     * @param array<string, string>   $classes     definition => class, for every generated definition
     */
    public static function resolve(array $schema, array $definitions, array $classes, string $where): self
    {
        foreach (['allOf', 'anyOf', 'oneOf', 'not'] as $keyword) {
            if (isset($schema[$keyword])) {
                throw new SpecException(sprintf('%s: %s is not supported', $where, $keyword));
            }
        }
        if (isset($schema['$ref'])) {
            $name = ModelSet::refName($schema['$ref']);
            $target = Json::object($definitions[$name] ?? null, $name);
            $class = $classes[$name] ?? null;
            if ($class !== null && ($target['type'] ?? null) === 'object') {
                return new self('object', 'one', $class);
            }
            if ($class !== null && ($target['type'] ?? null) === 'string' && isset($target['enum'])) {
                return new self('string', 'one', null, $class);
            }

            throw new SpecException(sprintf('%s: $ref to %s, which is neither an object nor a string enum', $where, $name));
        }
        if (!array_key_exists('type', $schema)) {
            throw new SpecException(sprintf('%s: the schema has no type', $where));
        }

        switch ($schema['type']) {
            case 'string':
                if (isset($schema['enum'])) {
                    throw new SpecException(sprintf('%s: an inline enum is not supported; declare it as a definition', $where));
                }

                return new self('string', 'one');
            case 'integer':
                return new self('int', 'one');
            case 'number':
                return new self('number', 'one');
            case 'boolean':
                return new self('bool', 'one');
            case 'array':
                if (!isset($schema['items'])) {
                    throw new SpecException(sprintf('%s: an array needs items', $where));
                }
                $item = self::resolve(Json::object($schema['items'], $where . '[]'), $definitions, $classes, $where . '[]');
                if ($item->container !== 'one') {
                    throw new SpecException(sprintf('%s: a %s is not supported', $where, $item->container === 'list' ? 'nested array' : 'list of maps'));
                }

                return new self($item->kind, 'list', $item->class, $item->enum);
            case 'object':
                if (isset($schema['properties'])) {
                    throw new SpecException(sprintf('%s: an inline object is not supported; declare it as a definition', $where));
                }
                if (!isset($schema['additionalProperties']) || is_bool($schema['additionalProperties'])) {
                    throw new SpecException(sprintf('%s: a free-form object is not supported', $where));
                }
                $item = self::resolve(Json::object($schema['additionalProperties'], $where . '{}'), $definitions, $classes, $where . '{}');
                if ($item->container !== 'one') {
                    throw new SpecException(sprintf('%s: a map of %ss is not supported', $where, $item->container === 'list' ? 'list' : 'map'));
                }

                return new self($item->kind, 'map', $item->class, $item->enum);
            default:
                throw new SpecException(sprintf('%s: the type %s is not supported', $where, (string) json_encode($schema['type'])));
        }
    }

    /**
     * The native type of the promoted property.
     */
    public function phpType(string $namespace): string
    {
        if ($this->container !== 'one') {
            return '?array';
        }

        return match ($this->kind) {
            'number' => 'int|float|null',
            'object' => '?' . self::reference((string) $this->class, $namespace),
            default => '?' . $this->kind,
        };
    }

    /**
     * The PHPDoc type of the promoted property.
     */
    public function docType(string $namespace): string
    {
        $item = $this->kind === 'object' ? self::reference((string) $this->class, $namespace) : self::SCALARS[$this->kind];

        return match ($this->container) {
            'one' => $item . '|null',
            'list' => "list<$item>|null",
            'map' => "array<array-key, $item>|null",
        };
    }

    /**
     * The fromArray() expression that reads $key out of $data.
     */
    public function reader(string $key, string $namespace): string
    {
        $arguments = '$data, ' . var_export($key, true);
        if ($this->kind === 'object') {
            $arguments .= ', ' . self::reference((string) $this->class, $namespace) . '::class';
        }

        return sprintf('Fields::%s(%s)', self::READERS[$this->container][$this->kind], $arguments);
    }

    /**
     * A class as written in a file of $namespace.
     */
    public static function reference(string $class, string $namespace): string
    {
        $at = strrpos($class, '\\');
        $short = $at === false ? $class : substr($class, $at + 1);

        return $class === $namespace . '\\' . $short ? $short : '\\' . $class;
    }
}
