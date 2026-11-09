<?php

declare(strict_types=1);

namespace Ashio\Model;

class LicenseWithPriceVersion
{
    public function __construct(
        public readonly ?string $activation_code = null,
        public readonly ?string $billing_type = null,
        public readonly ?string $comment = null,
        public readonly ?string $contract = null,
        public readonly ?string $expiration_date = null,
        /** @var string[]|null */
        public readonly ?array $items = null,
        public readonly ?int $key_id = null,
        public readonly ?string $key_number = null,
        public readonly ?string $order_date = null,
        public readonly ?int $parent_key_id = null,
        public readonly ?int $period = null,
        public readonly ?string $product = null,
        /** @var array<string, bool>|null */
        public readonly ?array $sku_values = null,
        public readonly ?string $status = null,
        public readonly ?string $title = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            activation_code: $data['activation_code'] ?? null,
            billing_type: $data['billing_type'] ?? null,
            comment: $data['comment'] ?? null,
            contract: $data['contract'] ?? null,
            expiration_date: $data['expiration_date'] ?? null,
            items: $data['items'] ?? null,
            key_id: $data['key_id'] ?? null,
            key_number: $data['key_number'] ?? null,
            order_date: $data['order_date'] ?? null,
            parent_key_id: $data['parent_key_id'] ?? null,
            period: $data['period'] ?? null,
            product: $data['product'] ?? null,
            sku_values: $data['sku_values'] ?? null,
            status: $data['status'] ?? null,
            title: $data['title'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'activation_code' => $this->activation_code,
            'billing_type' => $this->billing_type,
            'comment' => $this->comment,
            'contract' => $this->contract,
            'expiration_date' => $this->expiration_date,
            'items' => $this->items,
            'key_id' => $this->key_id,
            'key_number' => $this->key_number,
            'order_date' => $this->order_date,
            'parent_key_id' => $this->parent_key_id,
            'period' => $this->period,
            'product' => $this->product,
            'sku_values' => $this->sku_values,
            'status' => $this->status,
            'title' => $this->title,
        ], fn ($v) => $v !== null);
    }
}
