<?php

namespace Vexura\Model;

class EasyDmarcOrder
{
    /** @var int|null */
    public $id;

    /** @var string|null */
    public $status;

    /** @var string|null */
    public $activated_at;

    /** @var string|null */
    public $expired_at;

    /** @var string|null */
    public $renew_at;

    /** @var EasyDmarcDomain|null */
    public $domain;

    /** @var string|null */
    public $easydmarc_email_address;

    /** @var string|null */
    public $record_type;

    /** @var string|null */
    public $record_host;

    /** @var string|null */
    public $record_value;

    /** @var EasyDmarcRecentAction|null */
    public $recent_easydmarc_action;

    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->activated_at = $data['activated_at'] ?? null;
        $this->expired_at = $data['expired_at'] ?? null;
        $this->renew_at = $data['renew_at'] ?? null;
        $this->easydmarc_email_address = $data['easydmarc_email_address'] ?? null;
        $this->record_type = $data['record_type'] ?? null;
        $this->record_host = $data['record_host'] ?? null;
        $this->record_value = $data['record_value'] ?? null;

        if (isset($data['domain'])) {
            $this->domain = (new EasyDmarcDomain())->fromArray($data['domain']);
        }

        if (isset($data['recent_easydmarc_action'])) {
            $this->recent_easydmarc_action = (new EasyDmarcRecentAction())->fromArray($data['recent_easydmarc_action']);
        }

        return $this;
    }
}