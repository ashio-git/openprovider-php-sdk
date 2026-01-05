<?php

declare(strict_types=1);

namespace Ashio\Model;

class Prices
{
    public function __construct(
        public readonly ?Price $product = null,
        public readonly ?Price $reseller = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            product: isset($data['product']) ? Price::fromArray($data['product']) : null,
            reseller: isset($data['reseller']) ? Price::fromArray($data['reseller']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'product' => $this->product?->toArray(),
            'reseller' => $this->reseller?->toArray(),
        ], fn($v) => $v !== null);
    }
}