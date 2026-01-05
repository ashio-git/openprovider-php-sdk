<?php

declare(strict_types=1);

namespace Ashio\Model;

class SelectOption
{
    public function __construct(
        public readonly ?string $description = null,
        public readonly ?string $value = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            description: $data['description'] ?? null,
            value: $data['value'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'description' => $this->description,
            'value' => $this->value,
        ], fn($v) => $v !== null);
    }
}