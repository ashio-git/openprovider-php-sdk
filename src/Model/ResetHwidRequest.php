<?php

declare(strict_types=1);

namespace Ashio\Model;

class ResetHwidRequest
{
    public function __construct(
        public readonly ?int $key_id = null,
        public readonly ?string $product = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            key_id: $data['key_id'] ?? null,
            product: $data['product'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'key_id' => $this->key_id,
            'product' => $this->product,
        ], fn($v) => $v !== null);
    }
}