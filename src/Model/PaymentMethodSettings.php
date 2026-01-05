<?php

declare(strict_types=1);

namespace Ashio\Model;

class PaymentMethodSettings
{
    public function __construct(
        public readonly ?PaymentMethodLimits $limit = null,
        public readonly ?float $transaction_fee = null,
        public readonly ?float $transaction_fee_absolute = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            limit: isset($data['limit']) ? PaymentMethodLimits::fromArray($data['limit']) : null,
            transaction_fee: $data['transaction_fee'] ?? null,
            transaction_fee_absolute: $data['transaction_fee_absolute'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'limit' => $this->limit?->toArray(),
            'transaction_fee' => $this->transaction_fee,
            'transaction_fee_absolute' => $this->transaction_fee_absolute,
        ], fn($v) => $v !== null);
    }
}