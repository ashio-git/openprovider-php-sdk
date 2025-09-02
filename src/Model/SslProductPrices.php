<?php

namespace Vexura\Model;

class SslProductPrices
{
    /**
     * @var int|null
     */
    public $period;

    /**
     * @var SslProductPriceGroup|null
     */
    public $price;

    /**
     * @var SslProductPriceGroup|null
     */
    public $extra_domain_price;

    /**
     * @var SslProductPriceGroup|null
     */
    public $extra_wildcard_domain_price;

    public function fromArray(array $data): self
    {
        $this->period = $data['period'] ?? null;
        if (isset($data['price'])) {
            $this->price = (new SslProductPriceGroup())->fromArray($data['price']);
        }
        if (isset($data['extra_domain_price'])) {
            $this->extra_domain_price = (new SslProductPriceGroup())->fromArray($data['extra_domain_price']);
        }
        if (isset($data['extra_wildcard_domain_price'])) {
            $this->extra_wildcard_domain_price = (new SslProductPriceGroup())->fromArray($data['extra_wildcard_domain_price']);
        }
        return $this;
    }
}