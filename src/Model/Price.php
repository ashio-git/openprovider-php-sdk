<?php

namespace Vexura\Model;

class Price
{
    /**
     * @var bool|null
     */
    public $is_premium;

    /**
     * @var bool|null
     */
    public $is_promotion;

    /**
     * @var PriceGroup|null
     */
    public $membership_price;

    /**
     * @var PriceGroup|null
     */
    public $price;

    /**
     * @var PromotionData|null
     */
    public $promotion_data;

    /**
     * @var PriceGroup|null
     */
    public $tier_price;

    public function fromArray(array $data): self
    {
        $this->is_premium = $data['is_premium'] ?? null;
        $this->is_promotion = $data['is_promotion'] ?? null;

        if (isset($data['membership_price'])) {
            $this->membership_price = (new PriceGroup())->fromArray($data['membership_price']);
        }

        if (isset($data['price'])) {
            $this->price = (new PriceGroup())->fromArray($data['price']);
        }

        if (isset($data['promotion_data'])) {
            $this->promotion_data = (new PromotionData())->fromArray($data['promotion_data']);
        }

        if (isset($data['tier_price'])) {
            $this->tier_price = (new PriceGroup())->fromArray($data['tier_price']);
        }

        return $this;
    }
}