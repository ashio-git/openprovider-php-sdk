<?php

namespace Vexura\Model;

class PriceGroup
{
    /**
     * @var ProductPrice|null
     */
    public $product;

    /**
     * @var ProductPrice|null
     */
    public $reseller;

    public function fromArray(array $data): self
    {
        if (isset($data['product'])) {
            $this->product = (new ProductPrice())->fromArray($data['product']);
        }
        if (isset($data['reseller'])) {
            $this->reseller = (new ProductPrice())->fromArray($data['reseller']);
        }
        return $this;
    }
}