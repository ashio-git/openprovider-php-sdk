<?php

declare(strict_types=1);

namespace Ashio\Model;

class RegistryStatus
{
    public function __construct(
        public readonly ?bool $is_changeable = null,
        public readonly ?int $value = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            is_changeable: $data['is_changeable'] ?? null,
            value: $data['value'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'is_changeable' => $this->is_changeable,
            'value' => $this->value,
        ], fn($v) => $v !== null);
    }
}