<?php

declare(strict_types=1);

namespace Ashio\Model;

class SettingsPaymentMethodSettings
{
    public ?SettingsPaymentMethodLimits $limit = null;
    public ?float $transactionFee = null;
    public ?float $transactionFeeAbsolute = null;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        if (isset($data['limit'])) {
            $this->limit = (new SettingsPaymentMethodLimits())->fromArray($data['limit']);
        }
        $this->transactionFee = $data['transaction_fee'] ?? null;
        $this->transactionFeeAbsolute = $data['transaction_fee_absolute'] ?? null;

        return $this;
    }

    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        $data = [
            'transaction_fee' => $this->transactionFee,
            'transaction_fee_absolute' => $this->transactionFeeAbsolute,
        ];
        if ($this->limit) {
            $data['limit'] = $this->limit->toArray();
        }

        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }
}
