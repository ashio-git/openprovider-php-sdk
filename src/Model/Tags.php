<?php

declare(strict_types=1);

namespace Ashio\Model;

class Tags
{
    public function __construct(
        public readonly ?string $key = null,
        public readonly ?string $value = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'] ?? null,
            value: $data['value'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'key' => $this->key,
            'value' => $this->value,
        ], fn($v) => $v !== null);
    }
}