<?php

declare(strict_types=1);

namespace Vexura\Model;

class SettingsPaymentMethodLimits
{
    public ?SettingsPaymentMethodLimit $maximum = null;
    public ?SettingsPaymentMethodLimit $minimum = null;

    public function fromArray(array $data): self
    {
        if (isset($data['maximum'])) {
            $this->maximum = (new SettingsPaymentMethodLimit())->fromArray($data['maximum']);
        }
        if (isset($data['minimum'])) {
            $this->minimum = (new SettingsPaymentMethodLimit())->fromArray($data['minimum']);
        }
        return $this;
    }

    public function toArray(): array
    {
        $data = [];
        if ($this->maximum) {
            $data['maximum'] = $this->maximum->toArray();
        }
        if ($this->minimum) {
            $data['minimum'] = $this->minimum->toArray();
        }
        return $data;
    }
}
