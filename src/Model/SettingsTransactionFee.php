<?php

declare(strict_types=1);

namespace Ashio\Model;

class SettingsTransactionFee
{
    public ?float $bank = null;
    public ?float $creditCard = null;
    public ?float $ideal = null;
    public ?float $paypal = null;
    public ?float $westernUnion = null;
    public ?float $yandex = null;

    public function fromArray(array $data): self
    {
        $this->bank = $data['bank'] ?? null;
        $this->creditCard = $data['credit_card'] ?? null;
        $this->ideal = $data['ideal'] ?? null;
        $this->paypal = $data['paypal'] ?? null;
        $this->westernUnion = $data['western_union'] ?? null;
        $this->yandex = $data['yandex'] ?? null;
        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'bank' => $this->bank,
            'credit_card' => $this->creditCard,
            'ideal' => $this->ideal,
            'paypal' => $this->paypal,
            'western_union' => $this->westernUnion,
            'yandex' => $this->yandex,
        ], function ($value) {
            return $value !== null;
        });
    }
}
