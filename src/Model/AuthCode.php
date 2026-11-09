<?php

namespace Ashio\Model;

class AuthCode
{
    /**
     * @var string|null
     */
    public $auth_code;

    /**
     * @var bool|null
     */
    public $success;

    /**
     * @var string|null
     */
    public $type;

    /**
     * @var AuthInfo|null
     */
    public $auth_info;

    /**
     * @var string|null
     */
    public $domain_roid;

    /**
     * @var string[]|null
     */
    public $epp_statuses;

    /**
     * @var RgpStatuses|null
     */
    public $rgp_statuses;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->auth_code = $data['auth_code'] ?? null;
        $this->success = $data['success'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->domain_roid = $data['domain_roid'] ?? null;
        $this->epp_statuses = $data['epp_statuses'] ?? null;

        if (isset($data['auth_info'])) {
            $this->auth_info = (new AuthInfo())->fromArray($data['auth_info']);
        }

        if (isset($data['rgp_statuses'])) {
            $this->rgp_statuses = (new RgpStatuses())->fromArray($data['rgp_statuses']);
        }

        return $this;
    }
}
