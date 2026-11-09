<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Spec;

use Ashio\OpenProvider\Exception\InvalidArgumentException;

/**
 * One parameter of an operation, as the spec declares it.
 */
final class ParameterSpec
{
    public const SCALAR_TYPES = ['string', 'integer', 'number', 'boolean'];
    public const COLLECTION_FORMATS = ['csv' => ',', 'ssv' => ' ', 'tsv' => "\t", 'pipes' => '|', 'multi' => ''];

    /**
     * @param string|null $type             a Swagger type for path and query parameters; null for the body
     * @param string|null $itemType         the item type of an array
     * @param string      $collectionFormat how an array goes into the query (Swagger's default is csv)
     */
    public function __construct(
        public readonly string $name,
        public readonly ParameterLocation $in,
        public readonly bool $required = false,
        public readonly ?string $type = null,
        public readonly ?string $itemType = null,
        public readonly string $collectionFormat = 'csv',
    ) {
        if ($name === '') {
            throw new InvalidArgumentException('A parameter needs a name');
        }
        if ($in === ParameterLocation::Body) {
            return;
        }
        if ($type === 'array') {
            if (!in_array($itemType, self::SCALAR_TYPES, true)) {
                throw new InvalidArgumentException(sprintf('Array parameter "%s" needs a scalar item type', $name));
            }
            if (!array_key_exists($collectionFormat, self::COLLECTION_FORMATS)) {
                throw new InvalidArgumentException(sprintf('Parameter "%s" has an unknown collectionFormat "%s"', $name, $collectionFormat));
            }
            if ($in === ParameterLocation::Path) {
                throw new InvalidArgumentException(sprintf('Path parameter "%s" cannot be an array', $name));
            }
        } elseif (!in_array($type, self::SCALAR_TYPES, true)) {
            throw new InvalidArgumentException(sprintf('Parameter "%s" has the unsupported type "%s"', $name, $type ?? 'none'));
        }
    }

    /**
     * @param array<array-key, mixed> $parameter a Swagger 2.0 parameter object
     */
    public static function fromSwagger(array $parameter, string $context): self
    {
        $name = $parameter['name'] ?? null;
        $in = $parameter['in'] ?? null;
        if (!is_string($name) || !is_string($in)) {
            throw new InvalidArgumentException($context . ': a parameter needs a string name and in');
        }
        $location = ParameterLocation::tryFrom($in)
            ?? throw new InvalidArgumentException(sprintf('%s: parameter "%s" is in %s, which the SDK does not support', $context, $name, $in));
        $type = $parameter['type'] ?? null;
        $items = $parameter['items'] ?? null;
        $itemType = is_array($items) ? ($items['type'] ?? null) : null;
        $format = $parameter['collectionFormat'] ?? 'csv';

        return new self(
            $name,
            $location,
            $location === ParameterLocation::Path || ($parameter['required'] ?? false) === true,
            $location === ParameterLocation::Body ? null : (is_string($type) ? $type : null),
            is_string($itemType) ? $itemType : null,
            is_string($format) ? $format : 'csv',
        );
    }
}
