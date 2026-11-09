<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Support;

use Ashio\OpenProvider\Serialization\Fields;
use Ashio\OpenProvider\Serialization\Model;

/**
 * The smallest model with the generated shape, so the Fields tests do not depend on the spec.
 */
final readonly class SampleModel implements Model
{
    /**
     * @param array<array-key, mixed> $extra
     */
    public function __construct(
        public ?int $id = null,
        public array $extra = [],
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    #[\Override]
    public static function fromArray(array $data): static
    {
        return new self(
            id: Fields::int($data, 'id'),
            extra: $data,
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    #[\Override]
    public function toArray(): array
    {
        return Fields::toArray(['id' => $this->id], $this->extra);
    }

    #[\Override]
    public function jsonSerialize(): \stdClass
    {
        return Fields::toJson(['id' => $this->id], $this->extra);
    }
}
