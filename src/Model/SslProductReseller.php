<?php

namespace Ashio\Model;

class SslProductReseller
{
    /**
     * @var string|null
     */
    public $currency;

    /**
     * @var float|null
     */
    public $price;

    public function fromArray(array $data): self
    {
        $this->currency = $data['currency'] ?? null;
        $this->price = $data['price'] ?? null;
        return $this;
    }
}