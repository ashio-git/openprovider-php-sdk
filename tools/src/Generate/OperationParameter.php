<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Generate;

use Ashio\OpenProvider\Tools\Spec\Json;
use Ashio\OpenProvider\Tools\Spec\SpecException;

/**
 * One parameter of an operation as the generator writes it: the spec's view (name, location,
 * type) and the PHP one (variable name, native and PHPDoc types).
 */
final class OperationParameter
{
    private const SCALARS = ['string' => 'string', 'integer' => 'int', 'number' => 'int|float', 'boolean' => 'bool'];
    private const COLLECTION_FORMATS = ['csv', 'ssv', 'tsv', 'pipes', 'multi'];

    /**
     * @param 'path'|'query'|'body' $in
     * @param string|null           $type      the Swagger type; null for the body
     * @param string|null           $itemType  the item type of an array
     * @param string|null           $bodyClass the model class of the body
     * @param list<string>          $enum      the values the parameter (or its items) takes
     */
    private function __construct(
        public readonly string $name,
        public readonly string $in,
        public readonly bool $required,
        public readonly ?string $type,
        public readonly ?string $itemType,
        public readonly string $collectionFormat,
        public readonly ?string $bodyClass,
        public readonly string $description,
        public readonly array $enum,
        public readonly bool $hasDefault,
        public readonly mixed $default,
        public readonly string $variable = '',
    ) {
    }

    /**
     * @param array<array-key, mixed> $parameter   a Swagger 2.0 parameter object
     * @param array<string, string>   $classes     definition => model class
     * @param array<array-key, mixed> $definitions
     */
    public static function fromSpec(array $parameter, array $classes, array $definitions, string $operationId): self
    {
        $name = Json::string($parameter['name'] ?? null, "$operationId parameter name");
        $in = $parameter['in'] ?? null;
        if ($in !== 'path' && $in !== 'query' && $in !== 'body') {
            throw new SpecException(sprintf('%s: parameter "%s" is in %s, which the SDK does not support', $operationId, $name, (string) json_encode($in)));
        }
        $where = "$operationId parameter \"$name\"";
        $description = Json::optionalString($parameter, 'description') ?? '';
        $hasDefault = array_key_exists('default', $parameter);
        if ($in === 'body') {
            $schema = Json::object($parameter['schema'] ?? null, "$where schema");
            $definition = ModelSet::refName($schema['$ref'] ?? null);
            $class = $classes[$definition] ?? null;
            if ($class === null || (Json::object($definitions[$definition] ?? null, $definition)['type'] ?? null) !== 'object') {
                throw new SpecException(sprintf('%s: the body %s is not a generated model', $where, $definition));
            }

            return new self($name, $in, ($parameter['required'] ?? false) === true, null, null, 'csv', $class, $description, [], $hasDefault, $parameter['default'] ?? null);
        }

        $type = Json::string($parameter['type'] ?? null, "$where type");
        $itemType = null;
        $items = [];
        if ($type === 'array') {
            $items = Json::object($parameter['items'] ?? null, "$where items");
            $itemType = Json::string($items['type'] ?? null, "$where item type");
            if (!isset(self::SCALARS[$itemType])) {
                throw new SpecException(sprintf('%s: arrays of %s are not supported', $where, $itemType));
            }
            if ($in === 'path') {
                throw new SpecException($where . ': a path parameter cannot be an array');
            }
        } elseif (!isset(self::SCALARS[$type])) {
            throw new SpecException(sprintf('%s: the type %s is not supported', $where, $type));
        }
        $format = $parameter['collectionFormat'] ?? 'csv';
        if (!in_array($format, self::COLLECTION_FORMATS, true)) {
            throw new SpecException(sprintf('%s: unknown collectionFormat %s', $where, (string) json_encode($format)));
        }

        return new self(
            $name,
            $in,
            $in === 'path' || ($parameter['required'] ?? false) === true,
            $type,
            $itemType,
            $format,
            null,
            $description,
            array_map(
                static fn (mixed $v): string => is_scalar($v) ? (string) $v : (string) json_encode($v),
                Json::list($parameter['enum'] ?? $items['enum'] ?? [], "$where enum"),
            ),
            $hasDefault,
            $parameter['default'] ?? null,
        );
    }

    public function withVariable(string $variable): self
    {
        return new self(
            $this->name,
            $this->in,
            $this->required,
            $this->type,
            $this->itemType,
            $this->collectionFormat,
            $this->bodyClass,
            $this->description,
            $this->enum,
            $this->hasDefault,
            $this->default,
            $variable,
        );
    }

    /**
     * The native type in the method signature.
     *
     * @param callable(string): string $reference how a class is written in the generated file
     */
    public function phpType(callable $reference): string
    {
        $type = match (true) {
            $this->bodyClass !== null => $reference($this->bodyClass),
            $this->type === 'array' => 'array',
            default => self::SCALARS[(string) $this->type],
        };
        if ($this->required) {
            return $type;
        }

        return str_contains($type, '|') ? $type . '|null' : '?' . $type;
    }

    /**
     * The PHPDoc type of the @param line.
     *
     * @param callable(string): string $reference
     */
    public function docType(callable $reference): string
    {
        $type = match (true) {
            $this->bodyClass !== null => $reference($this->bodyClass),
            $this->type === 'array' => 'list<' . self::SCALARS[(string) $this->itemType] . '>',
            default => self::SCALARS[(string) $this->type],
        };

        return $this->required ? $type : $type . '|null';
    }

    /**
     * What the @param line says after the variable: the spec's description, then the API name
     * when it differs, the values and the API's default.
     */
    public function doc(): string
    {
        $notes = [];
        if ($this->name !== $this->variable) {
            $notes[] = "`$this->name`";
        }
        if ($this->enum !== []) {
            $notes[] = 'one of: ' . implode(', ', $this->enum);
        }
        if ($this->hasDefault) {
            $notes[] = 'API default: ' . (string) json_encode($this->default, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return trim(DocText::inline($this->description) . ($notes === [] ? '' : ' (' . DocText::sanitize(implode(', ', $notes)) . ')'));
    }

    /**
     * The `new ParameterSpec(...)` expression of the registry.
     */
    public function registryEntry(): string
    {
        $arguments = [
            var_export($this->name, true),
            'ParameterLocation::' . ucfirst($this->in),
            $this->required ? 'true' : 'false',
        ];
        if ($this->type !== null) {
            $arguments[] = var_export($this->type, true);
        }
        if ($this->type === 'array') {
            $arguments[] = var_export($this->itemType, true);
            $arguments[] = var_export($this->collectionFormat, true);
        }

        return 'new ParameterSpec(' . implode(', ', $arguments) . ')';
    }
}
