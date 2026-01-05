<?php

declare(strict_types=1);

namespace Ashio\Model;

class SectigoData
{
    public function __construct(
        public readonly ?bool $autorenew = null,
        public readonly ?string $order_date = null,
        public readonly ?string $renewal_date = null,
        public readonly ?bool $secured = null,
        public readonly ?int $website_id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            autorenew: $data['autorenew'] ?? null,
            order_date: $data['order_date'] ?? null,
            renewal_date: $data['renewal_date'] ?? null,
            secured: $data['secured'] ?? null,
            website_id: $data['website_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'autorenew' => $this->autorenew,
            'order_date' => $this->order_date,
            'renewal_date' => $this->renewal_date,
            'secured' => $this->secured,
            'website_id' => $this->website_id,
        ], fn($v) => $v !== null);
    }
}