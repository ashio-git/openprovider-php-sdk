<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Serialization;

/**
 * An API object as an immutable DTO. Every class under src/Model implements it, and all of them
 * are generated from spec/openprovider.json by `php tools/generate models`.
 *
 * Each model also has `public readonly array $extra`: what its typed properties cannot hold,
 * exactly as received (keys the spec does not declare, explicit nulls, values of another type).
 */
interface Model extends \JsonSerializable
{
    /**
     * Builds the model from a decoded API object. It never refuses content: a value the typed
     * properties cannot hold goes to `$extra`.
     *
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): static;

    /**
     * The API representation: the API's keys, absent fields left out, then `$extra`. For a model
     * built by fromArray() it equals the array it was built from.
     *
     * @return array<array-key, mixed>
     */
    public function toArray(): array;

    /**
     * The JSON object the API expects: nested models and maps stay objects even when empty.
     */
    #[\Override]
    public function jsonSerialize(): \stdClass;
}
