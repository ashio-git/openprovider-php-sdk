<?php

namespace Vexura\Model;

class CheckDomainResult
{
    /**
     * @var string|null
     */
    public $claim_key;

    /**
     * @var string|null
     */
    public $domain;

    /**
     * @var bool|null
     */
    public $is_premium;

    /**
     * @var PremiumPrice|null
     */
    public $premium;

    /**
     * @var PriceGroup|null
     */
    public $price;

    /**
     * @var string|null
     */
    public $reason;

    /**
     * @var string|null
     */
    public $status;

    /**
     * @var string|null
     */
    public $whois;

    public function fromArray(array $data): self
    {
        $this->claim_key = $data['claim_key'] ?? null;
        $this->domain = $data['domain'] ?? null;
        $this->is_premium = $data['is_premium'] ?? null;
        $this->reason = $data['reason'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->whois = $data['whois'] ?? null;

        if (isset($data['premium'])) {
            $this->premium = (new PremiumPrice())->fromArray($data['premium']);
        }

        if (isset($data['price'])) {
            $this->price = (new PriceGroup())->fromArray($data['price']);
        }

        return $this;
    }
}