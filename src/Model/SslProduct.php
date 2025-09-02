<?php

namespace Vexura\Model;

class SslProduct
{
    /** @var int|null */
    public $id;

    /** @var string|null */
    public $name;

    /** @var string|null */
    public $brand_name;

    /** @var bool|null */
    public $bypass_san;

    /** @var string|null */
    public $category;

    /** @var string|null */
    public $sub_category;

    /** @var string|null */
    public $validation_method;

    /** @var string|null */
    public $delivery_time;

    /** @var string|null */
    public $description;

    /** @var string|null */
    public $encryption;

    /** @var string|null */
    public $free_refund_period;

    /** @var string|null */
    public $free_reissue_period;

    /** @var int|null */
    public $included_domains_count;

    /** @var bool|null */
    public $is_extended_validation_supported;

    /** @var bool|null */
    public $is_idn_supported;

    /** @var bool|null */
    public $is_mobile_supported;

    /** @var bool|null */
    public $is_sgc_supported;

    /** @var bool|null */
    public $is_wildcard_multidomain_supported;

    /** @var bool|null */
    public $is_wildcard_supported;

    /** @var int|null */
    public $max_domains;

    /** @var int|null */
    public $max_period;

    /** @var int|null */
    public $number_of_domains;

    /** @var string|null */
    public $order_module;

    /** @var SslProductPrices[]|null */
    public $prices;

    /** @var SslProductLevelPrices[]|null */
    public $level_prices;

    /** @var string|null */
    public $root;

    /** @var SslProductSupportedSoftware[]|null */
    public $supported_software;

    /** @var SslProductWarranty|null */
    public $warranty;

    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->brand_name = $data['brand_name'] ?? null;
        $this->bypass_san = $data['bypass_san'] ?? null;
        $this->category = $data['category'] ?? null;
        $this->sub_category = $data['sub_category'] ?? null;
        $this->validation_method = $data['validation_method'] ?? null;
        $this->delivery_time = $data['delivery_time'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->encryption = $data['encryption'] ?? null;
        $this->free_refund_period = $data['free_refund_period'] ?? null;
        $this->free_reissue_period = $data['free_reissue_period'] ?? null;
        $this->included_domains_count = $data['included_domains_count'] ?? null;
        $this->is_extended_validation_supported = $data['is_extended_validation_supported'] ?? null;
        $this->is_idn_supported = $data['is_idn_supported'] ?? null;
        $this->is_mobile_supported = $data['is_mobile_supported'] ?? null;
        $this->is_sgc_supported = $data['is_sgc_supported'] ?? null;
        $this->is_wildcard_multidomain_supported = $data['is_wildcard_multidomain_supported'] ?? null;
        $this->is_wildcard_supported = $data['is_wildcard_supported'] ?? null;
        $this->max_domains = $data['max_domains'] ?? null;
        $this->max_period = $data['max_period'] ?? null;
        $this->number_of_domains = $data['number_of_domains'] ?? null;
        $this->order_module = $data['order_module'] ?? null;
        $this->root = $data['root'] ?? null;

        if (isset($data['prices'])) {
            $this->prices = array_map(function ($price) {
                return (new SslProductPrices())->fromArray($price);
            }, $data['prices']);
        }

        if (isset($data['level_prices'])) {
            $this->level_prices = array_map(function ($level_price) {
                return (new SslProductLevelPrices())->fromArray($level_price);
            }, $data['level_prices']);
        }

        if (isset($data['supported_software'])) {
            $this->supported_software = array_map(function ($software) {
                return (new SslProductSupportedSoftware())->fromArray($software);
            }, $data['supported_software']);
        }

        if (isset($data['warranty'])) {
            $this->warranty = (new SslProductWarranty())->fromArray($data['warranty']);
        }

        return $this;
    }
}