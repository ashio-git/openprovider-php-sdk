<?php

namespace Vexura\Model;

class PremiumPrice
{
    /**
     * @var string|null
     */
    public $currency;

    /**
     * @var OperationPrice|null
     */
    public $price;

    public function fromArray(array $data): self
    {
        $this->currency = $data['currency'] ?? null;
        if (isset($data['price'])) {
            $this->price = (new OperationPrice())->fromArray($data['price']);
        }
        return $this;
    }
}