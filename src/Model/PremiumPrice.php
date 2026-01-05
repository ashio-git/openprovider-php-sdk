<?php

declare(strict_types=1);

namespace Ashio\Model;

class PremiumPrice
{
    public function __construct(
        public readonly ?string $currency = null,
        public readonly ?OperationPrice $price = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            currency: $data['currency'] ?? null,
            price: isset($data['price']) ? OperationPrice::fromArray($data['price']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'currency' => $this->currency,
            'price' => $this->price?->toArray(),
        ], fn($v) => $v !== null);
    }
}