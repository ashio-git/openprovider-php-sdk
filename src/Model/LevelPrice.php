<?php

declare(strict_types=1);

namespace Ashio\Model;

class LevelPrice
{
    public function __construct(
        public readonly ?int $level = null,
        public readonly ?Price $price = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            level: $data['level'] ?? null,
            price: isset($data['price']) ? Price::fromArray($data['price']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'level' => $this->level,
            'price' => $this->price?->toArray(),
        ], fn($v) => $v !== null);
    }
}