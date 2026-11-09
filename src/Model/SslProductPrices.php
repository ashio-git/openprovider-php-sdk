<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslProductPrices
{
    public function __construct(
        public readonly ?SslProductPriceGroup $extra_domain_price = null,
        public readonly ?SslProductPriceGroup $extra_wildcard_domain_price = null,
        public readonly ?int $period = null,
        public readonly ?SslProductPriceGroup $price = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            extra_domain_price: isset($data['extra_domain_price']) ? SslProductPriceGroup::fromArray($data['extra_domain_price']) : null,
            extra_wildcard_domain_price: isset($data['extra_wildcard_domain_price']) ? SslProductPriceGroup::fromArray($data['extra_wildcard_domain_price']) : null,
            period: $data['period'] ?? null,
            price: isset($data['price']) ? SslProductPriceGroup::fromArray($data['price']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'extra_domain_price' => $this->extra_domain_price?->toArray(),
            'extra_wildcard_domain_price' => $this->extra_wildcard_domain_price?->toArray(),
            'period' => $this->period,
            'price' => $this->price?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
