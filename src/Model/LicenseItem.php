<?php

declare(strict_types=1);

namespace Ashio\Model;

class LicenseItem
{
    public function __construct(
        public readonly ?string $item = null,
        public readonly ?string $product = null,
        public readonly ?array $sku_values = null,
        public readonly ?string $title = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            item: $data['item'] ?? null,
            product: $data['product'] ?? null,
            sku_values: $data['sku_values'] ?? null,
            title: $data['title'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'item' => $this->item,
            'product' => $this->product,
            'sku_values' => $this->sku_values,
            'title' => $this->title,
        ], fn($v) => $v !== null);
    }
}