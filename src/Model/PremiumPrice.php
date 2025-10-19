<?php

namespace Ashio\Model;

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

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->currency = $data['currency'] ?? null;
        if (isset($data['price'])) {
            $this->price = (new OperationPrice())->fromArray($data['price']);
        }

        return $this;
    }
}
