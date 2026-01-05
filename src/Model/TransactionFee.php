<?php

declare(strict_types=1);

namespace Ashio\Model;

class TransactionFee
{
    public function __construct(
        public readonly ?float $bank = null,
        public readonly ?float $credit_card = null,
        public readonly ?float $ideal = null,
        public readonly ?float $paypal = null,
        public readonly ?float $western_union = null,
        public readonly ?float $yandex = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            bank: $data['bank'] ?? null,
            credit_card: $data['credit_card'] ?? null,
            ideal: $data['ideal'] ?? null,
            paypal: $data['paypal'] ?? null,
            western_union: $data['western_union'] ?? null,
            yandex: $data['yandex'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'bank' => $this->bank,
            'credit_card' => $this->credit_card,
            'ideal' => $this->ideal,
            'paypal' => $this->paypal,
            'western_union' => $this->western_union,
            'yandex' => $this->yandex,
        ], fn($v) => $v !== null);
    }
}