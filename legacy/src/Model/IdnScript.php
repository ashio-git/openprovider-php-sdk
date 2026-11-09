<?php

declare(strict_types=1);

namespace Ashio\Model;

class IdnScript
{
    public function __construct(
        public readonly ?string $description = null,
        public readonly ?string $value = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            description: $data['description'] ?? null,
            value: $data['value'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'description' => $this->description,
            'value' => $this->value,
        ], fn ($v) => $v !== null);
    }
}
