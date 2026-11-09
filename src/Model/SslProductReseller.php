<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslProductReseller
{
    public function __construct(
        public readonly ?string $currency = null,
        public readonly ?float $price = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            currency: $data['currency'] ?? null,
            price: $data['price'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'currency' => $this->currency,
            'price' => $this->price,
        ], fn ($v) => $v !== null);
    }
}
