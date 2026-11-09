<?php

declare(strict_types=1);

namespace Ashio\Model;

class PaymentMethodLimit
{
    public function __construct(
        public readonly ?float $AUD = null,
        public readonly ?float $BGN = null,
        public readonly ?float $BRL = null,
        public readonly ?float $CAD = null,
        public readonly ?float $CHF = null,
        public readonly ?float $CNY = null,
        public readonly ?float $CZK = null,
        public readonly ?float $DKK = null,
        public readonly ?float $EEK = null,
        public readonly ?float $EUR = null,
        public readonly ?float $GBP = null,
        public readonly ?float $GHS = null,
        public readonly ?float $HKD = null,
        public readonly ?float $HRK = null,
        public readonly ?float $HUF = null,
        public readonly ?float $IDR = null,
        public readonly ?float $ILS = null,
        public readonly ?float $INR = null,
        public readonly ?float $JPY = null,
        public readonly ?float $KRW = null,
        public readonly ?float $LKR = null,
        public readonly ?float $LTL = null,
        public readonly ?float $LVL = null,
        public readonly ?float $MXN = null,
        public readonly ?float $MYR = null,
        public readonly ?float $NOK = null,
        public readonly ?float $NZD = null,
        public readonly ?float $PHP = null,
        public readonly ?float $PLN = null,
        public readonly ?float $RON = null,
        public readonly ?float $RUB = null,
        public readonly ?float $SEK = null,
        public readonly ?float $SGD = null,
        public readonly ?float $THB = null,
        public readonly ?float $TRY = null,
        public readonly ?float $USD = null,
        public readonly ?float $ZAR = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            AUD: $data['AUD'] ?? null,
            BGN: $data['BGN'] ?? null,
            BRL: $data['BRL'] ?? null,
            CAD: $data['CAD'] ?? null,
            CHF: $data['CHF'] ?? null,
            CNY: $data['CNY'] ?? null,
            CZK: $data['CZK'] ?? null,
            DKK: $data['DKK'] ?? null,
            EEK: $data['EEK'] ?? null,
            EUR: $data['EUR'] ?? null,
            GBP: $data['GBP'] ?? null,
            GHS: $data['GHS'] ?? null,
            HKD: $data['HKD'] ?? null,
            HRK: $data['HRK'] ?? null,
            HUF: $data['HUF'] ?? null,
            IDR: $data['IDR'] ?? null,
            ILS: $data['ILS'] ?? null,
            INR: $data['INR'] ?? null,
            JPY: $data['JPY'] ?? null,
            KRW: $data['KRW'] ?? null,
            LKR: $data['LKR'] ?? null,
            LTL: $data['LTL'] ?? null,
            LVL: $data['LVL'] ?? null,
            MXN: $data['MXN'] ?? null,
            MYR: $data['MYR'] ?? null,
            NOK: $data['NOK'] ?? null,
            NZD: $data['NZD'] ?? null,
            PHP: $data['PHP'] ?? null,
            PLN: $data['PLN'] ?? null,
            RON: $data['RON'] ?? null,
            RUB: $data['RUB'] ?? null,
            SEK: $data['SEK'] ?? null,
            SGD: $data['SGD'] ?? null,
            THB: $data['THB'] ?? null,
            TRY: $data['TRY'] ?? null,
            USD: $data['USD'] ?? null,
            ZAR: $data['ZAR'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'AUD' => $this->AUD,
            'BGN' => $this->BGN,
            'BRL' => $this->BRL,
            'CAD' => $this->CAD,
            'CHF' => $this->CHF,
            'CNY' => $this->CNY,
            'CZK' => $this->CZK,
            'DKK' => $this->DKK,
            'EEK' => $this->EEK,
            'EUR' => $this->EUR,
            'GBP' => $this->GBP,
            'GHS' => $this->GHS,
            'HKD' => $this->HKD,
            'HRK' => $this->HRK,
            'HUF' => $this->HUF,
            'IDR' => $this->IDR,
            'ILS' => $this->ILS,
            'INR' => $this->INR,
            'JPY' => $this->JPY,
            'KRW' => $this->KRW,
            'LKR' => $this->LKR,
            'LTL' => $this->LTL,
            'LVL' => $this->LVL,
            'MXN' => $this->MXN,
            'MYR' => $this->MYR,
            'NOK' => $this->NOK,
            'NZD' => $this->NZD,
            'PHP' => $this->PHP,
            'PLN' => $this->PLN,
            'RON' => $this->RON,
            'RUB' => $this->RUB,
            'SEK' => $this->SEK,
            'SGD' => $this->SGD,
            'THB' => $this->THB,
            'TRY' => $this->TRY,
            'USD' => $this->USD,
            'ZAR' => $this->ZAR,
        ], fn ($v) => $v !== null);
    }
}
