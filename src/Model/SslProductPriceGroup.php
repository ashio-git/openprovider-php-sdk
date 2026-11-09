<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslProductPriceGroup
{
    public function __construct(
        public readonly ?SslProductPrice $product = null,
        public readonly ?SslProductReseller $reseller = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            product: isset($data['product']) ? SslProductPrice::fromArray($data['product']) : null,
            reseller: isset($data['reseller']) ? SslProductReseller::fromArray($data['reseller']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'product' => $this->product?->toArray(),
            'reseller' => $this->reseller?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
