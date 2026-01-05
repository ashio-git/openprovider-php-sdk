<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslProductLevelPrices
{
    public function __construct(
        public readonly ?int $level = null,
        public readonly ?string $name = null,
        /** @var SslProductPrices[]|null */
        public readonly ?array $prices = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            level: $data['level'] ?? null,
            name: $data['name'] ?? null,
            prices: isset($data['prices']) ? array_map(fn(array $item) => SslProductPrices::fromArray($item), $data['prices']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'level' => $this->level,
            'name' => $this->name,
            'prices' => $this->prices !== null ? array_map(fn(SslProductPrices $item) => $item->toArray(), $this->prices) : null,
        ], fn($v) => $v !== null);
    }
}