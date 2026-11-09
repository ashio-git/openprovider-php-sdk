<?php

namespace Ashio\Model;

class Domain
{
    public ?int $id = null;
    public ?DomainAbuseDetails $abuse_details = null;
    public ?string $active_date = null;
    /** @var array<string, mixed>|null */
    public ?array $additional_data = null;
    public ?string $admin_handle = null;
    /** @var array<int, mixed>|null */
    public ?array $api_history = null;
    public ?string $application_id = null;
    public ?string $application_mode = null;
    public ?string $auth_code = null;
    public ?string $autorenew = null;
    public ?string $billing_handle = null;
    public ?bool $can_renew = null;
    public ?string $comments = null;
    public ?string $creation_date = null;
    public ?string $delete_status = null;
    public ?string $deleted_at = null;
    public ?string $dnssec = null;
    /** @var DomainDnssecKey[]|null */
    public ?array $dnssec_keys = null;
    public ?DomainName $domain = null;
    public ?string $expiration_date = null;
    public ?bool $has_history = null;
    /** @var array<int, mixed>|null */
    public ?array $history = null;
    public ?string $internal_auth_code = null;
    public ?bool $is_abusive = null;
    public ?string $is_client_hold_enabled = null;
    public ?bool $is_deleted = null;
    public ?bool $is_dnssec_enabled = null;
    public ?bool $is_locked = null;
    public ?bool $is_premium = null;
    public ?bool $is_private_whois_enabled = null;
    public ?bool $is_private_whois_allowed = null;
    public ?bool $is_spamexperts_enabled = null;
    public ?string $last_changed = null;
    /** @var Nameserver[]|null */
    public ?array $name_servers = null;
    public ?string $ns_group = null;
    public ?int $ns_template_id = null;
    public ?string $ns_template_name = null;
    public ?string $order_date = null;
    public ?DomainOwner $owner = null;
    public ?string $owner_handle = null;
    public ?string $quarantine_expiration_date = null;
    public ?string $renewal_date = null;
    public ?string $reseller_handle = null;
    public ?int $reseller_id = null;
    public ?DomainRegistryStatuses $registry_statuses = null;
    public ?string $status = null;
    public ?DomainWhoisPrivacyData $whois_privacy_data = null;
    public ?string $tech_handle = null;
    public ?bool $trade_allowed = null;
    public ?string $type = null;
    public ?bool $use_domicile = null;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        if ($data) {
            $this->fromArray($data);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        if (isset($data['abuse_details'])) {
            $this->abuse_details = (new DomainAbuseDetails())->fromArray($data['abuse_details']);
        }
        $this->active_date = $data['active_date'] ?? null;
        $this->additional_data = $data['additional_data'] ?? null;
        $this->admin_handle = $data['admin_handle'] ?? null;
        $this->api_history = $data['api_history'] ?? null;
        $this->application_id = $data['application_id'] ?? null;
        $this->application_mode = $data['application_mode'] ?? null;
        $this->auth_code = $data['auth_code'] ?? null;
        $this->autorenew = $data['autorenew'] ?? null;
        $this->billing_handle = $data['billing_handle'] ?? null;
        $this->can_renew = $data['can_renew'] ?? null;
        $this->comments = $data['comments'] ?? null;
        $this->creation_date = $data['creation_date'] ?? null;
        $this->delete_status = $data['delete_status'] ?? null;
        $this->deleted_at = $data['deleted_at'] ?? null;
        $this->dnssec = $data['dnssec'] ?? null;
        if (isset($data['dnssec_keys'])) {
            $this->dnssec_keys = array_map(static function ($row) {
                return (new DomainDnssecKey())->fromArray($row);
            }, $data['dnssec_keys']);
        }
        if (isset($data['domain'])) {
            $this->domain = (new DomainName())->fromArray($data['domain']);
        }
        $this->expiration_date = $data['expiration_date'] ?? null;
        $this->has_history = $data['has_history'] ?? null;
        $this->history = $data['history'] ?? null;
        $this->internal_auth_code = $data['internal_auth_code'] ?? null;
        $this->is_abusive = $data['is_abusive'] ?? null;
        $this->is_client_hold_enabled = $data['is_client_hold_enabled'] ?? null;
        $this->is_deleted = $data['is_deleted'] ?? null;
        $this->is_dnssec_enabled = $data['is_dnssec_enabled'] ?? null;
        $this->is_locked = $data['is_locked'] ?? null;
        $this->is_premium = $data['is_premium'] ?? null;
        $this->is_private_whois_enabled = $data['is_private_whois_enabled'] ?? null;
        $this->is_private_whois_allowed = $data['is_private_whois_allowed'] ?? null;
        $this->is_spamexperts_enabled = $data['is_spamexperts_enabled'] ?? null;
        $this->last_changed = $data['last_changed'] ?? null;
        if (isset($data['name_servers'])) {
            $this->name_servers = array_map(static function ($row) {
                return (new Nameserver())->fromArray($row);
            }, $data['name_servers']);
        }
        $this->ns_group = $data['ns_group'] ?? null;
        $this->ns_template_id = $data['ns_template_id'] ?? null;
        $this->ns_template_name = $data['ns_template_name'] ?? null;
        $this->order_date = $data['order_date'] ?? null;
        if (isset($data['owner'])) {
            $this->owner = (new DomainOwner())->fromArray($data['owner']);
        }
        $this->owner_handle = $data['owner_handle'] ?? null;
        $this->quarantine_expiration_date = $data['quarantine_expiration_date'] ?? null;
        $this->renewal_date = $data['renewal_date'] ?? null;
        $this->reseller_handle = $data['reseller_handle'] ?? null;
        $this->reseller_id = $data['reseller_id'] ?? null;
        if (isset($data['registry_statuses'])) {
            $this->registry_statuses = (new DomainRegistryStatuses())->fromArray($data['registry_statuses']);
        }
        $this->status = $data['status'] ?? null;
        if (isset($data['whois_privacy_data'])) {
            $this->whois_privacy_data = (new DomainWhoisPrivacyData())->fromArray($data['whois_privacy_data']);
        }
        $this->tech_handle = $data['tech_handle'] ?? null;
        $this->trade_allowed = $data['trade_allowed'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->use_domicile = $data['use_domicile'] ?? null;

        return $this;
    }
}
