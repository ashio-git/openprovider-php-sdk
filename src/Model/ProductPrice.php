<?php

namespace Ashio\Model;

class ProductPrice
{
    /**
     * @var string|null
     */
    public $currency;

    /**
     * @var float|null
     */
    public $price;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->currency = $data['currency'] ?? null;
        $this->price = $data['price'] ?? null;

        return $this;
    }
}
