<?php

declare(strict_types=1);

namespace Ashio\Model;

class TacContract
{
    public function __construct(
        public readonly ?bool $is_signed = null,
        public readonly ?string $name = null,
        public readonly ?string $signed_at = null,
        public readonly ?string $type = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            is_signed: $data['is_signed'] ?? null,
            name: $data['name'] ?? null,
            signed_at: $data['signed_at'] ?? null,
            type: $data['type'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'is_signed' => $this->is_signed,
            'name' => $this->name,
            'signed_at' => $this->signed_at,
            'type' => $this->type,
        ], fn ($v) => $v !== null);
    }
}
