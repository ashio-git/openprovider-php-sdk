<?php

declare(strict_types=1);

namespace Vexura\Model;

class SettingsPaymentMethodLimit
{
    public ?float $AUD = null;
    public ?float $BGN = null;
    public ?float $BRL = null;
    public ?float $CAD = null;
    public ?float $CHF = null;
    public ?float $CNY = null;
    public ?float $CZK = null;
    public ?float $DKK = null;
    public ?float $EEK = null;
    public ?float $EUR = null;
    public ?float $GBP = null;
    public ?float $GHS = null;
    public ?float $HKD = null;
    public ?float $HRK = null;
    public ?float $HUF = null;
    public ?float $IDR = null;
    public ?float $ILS = null;
    public ?float $INR = null;
    public ?float $JPY = null;
    public ?float $KRW = null;
    public ?float $LKR = null;
    public ?float $LTL = null;
    public ?float $LVL = null;
    public ?float $MXN = null;
    public ?float $MYR = null;
    public ?float $NOK = null;
    public ?float $NZD = null;
    public ?float $PHP = null;
    public ?float $PLN = null;
    public ?float $RON = null;
    public ?float $RUB = null;
    public ?float $SEK = null;
    public ?float $SGD = null;
    public ?float $THB = null;
    public ?float $TRY = null;
    public ?float $USD = null;
    public ?float $ZAR = null;

    public function fromArray(array $data): self
    {
        foreach ($data as $currency => $value) {
            if (property_exists($this, $currency)) {
                $this->{$currency} = $value;
            }
        }
        return $this;
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this));
    }
}
