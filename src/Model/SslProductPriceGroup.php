<?php

namespace Vexura\Model;

class SslProductPriceGroup
{
    /**
     * @var SslProductPrice|null
     */
    public $product;

    /**
     * @var SslProductReseller|null
     */
    public $reseller;

    public function fromArray(array $data): self
    {
        if (isset($data['product'])) {
            $this->product = (new SslProductPrice())->fromArray($data['product']);
        }
        if (isset($data['reseller'])) {
            $this->reseller = (new SslProductReseller())->fromArray($data['reseller']);
        }
        return $this;
    }
}