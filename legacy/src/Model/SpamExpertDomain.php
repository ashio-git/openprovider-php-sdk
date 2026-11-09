<?php

namespace Ashio\Model;

class SpamExpertDomain
{
    /** @var int|null */
    public $id;

    /** @var int|null */
    public $order_id;

    /** @var int|null */
    public $reseller_id;

    /** @var string|null */
    public $domain_name;

    /** @var string|null */
    public $expiration_date;

    /** @var bool|null */
    public $is_active;

    /** @var bool|null */
    public $bundle;

    /** @var bool|null */
    public $with_outgoing_filter;

    /** @var string|null */
    public $user_email;

    /** @var string|null */
    public $user_password;

    /** @var string|null */
    public $smtp_password;

    /** @var SpamExpertProducts|null */
    public $products;

    /** @var SpamExpertRecords|null */
    public $records;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->order_id = $data['order_id'] ?? null;
        $this->reseller_id = $data['reseller_id'] ?? null;
        $this->domain_name = $data['domain_name'] ?? null;
        $this->expiration_date = $data['expiration_date'] ?? null;
        $this->is_active = $data['is_active'] ?? null;
        $this->bundle = $data['bundle'] ?? null;
        $this->with_outgoing_filter = $data['with_outgoing_filter'] ?? null;
        $this->user_email = $data['user_email'] ?? null;
        $this->user_password = $data['user_password'] ?? null;
        $this->smtp_password = $data['smtp_password'] ?? null;

        if (isset($data['products'])) {
            $this->products = (new SpamExpertProducts())->fromArray($data['products']);
        }

        if (isset($data['records'])) {
            $this->records = (new SpamExpertRecords())->fromArray($data['records']);
        }

        return $this;
    }
}
