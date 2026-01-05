<?php

declare(strict_types=1);

namespace Ashio\Model;

class UpdateDomainRequest
{
    public function __construct(
        public readonly ?float $accept_update_fee = null,
        public readonly ?UpdateAdditionalData $additional_data = null,
        public readonly ?string $admin_handle = null,
        public readonly ?string $auth_code = null,
        public readonly ?string $autorenew = null,
        public readonly ?string $billing_handle = null,
        public readonly ?string $comments = null,
        /** @var DnssecKey[]|null */
        public readonly ?array $dnssec_keys = null,
        public readonly ?Domain $domain = null,
        public readonly ?bool $force_registry_update = null,
        public readonly ?int $id = null,
        public readonly ?bool $is_dnssec_enabled = null,
        public readonly ?bool $is_locked = null,
        public readonly ?bool $is_private_whois_enabled = null,
        public readonly ?bool $is_sectigo_dns_enabled = null,
        public readonly ?bool $is_spamexperts_enabled = null,
        /** @var Nameserver[]|null */
        public readonly ?array $name_servers = null,
        public readonly ?string $ns_group = null,
        public readonly ?int $ns_template_id = null,
        public readonly ?string $ns_template_name = null,
        public readonly ?string $owner_handle = null,
        public readonly ?bool $remove_nses = null,
        public readonly ?string $reseller_handle = null,
        public readonly ?bool $reset_auth_code = null,
        public readonly ?string $scheduled_at = null,
        public readonly ?string $tech_handle = null,
        public readonly ?bool $use_domicile = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            accept_update_fee: $data['accept_update_fee'] ?? null,
            additional_data: isset($data['additional_data']) ? UpdateAdditionalData::fromArray($data['additional_data']) : null,
            admin_handle: $data['admin_handle'] ?? null,
            auth_code: $data['auth_code'] ?? null,
            autorenew: $data['autorenew'] ?? null,
            billing_handle: $data['billing_handle'] ?? null,
            comments: $data['comments'] ?? null,
            dnssec_keys: isset($data['dnssec_keys']) ? array_map(fn(array $item) => DnssecKey::fromArray($item), $data['dnssec_keys']) : null,
            domain: isset($data['domain']) ? Domain::fromArray($data['domain']) : null,
            force_registry_update: $data['force_registry_update'] ?? null,
            id: $data['id'] ?? null,
            is_dnssec_enabled: $data['is_dnssec_enabled'] ?? null,
            is_locked: $data['is_locked'] ?? null,
            is_private_whois_enabled: $data['is_private_whois_enabled'] ?? null,
            is_sectigo_dns_enabled: $data['is_sectigo_dns_enabled'] ?? null,
            is_spamexperts_enabled: $data['is_spamexperts_enabled'] ?? null,
            name_servers: isset($data['name_servers']) ? array_map(fn(array $item) => Nameserver::fromArray($item), $data['name_servers']) : null,
            ns_group: $data['ns_group'] ?? null,
            ns_template_id: $data['ns_template_id'] ?? null,
            ns_template_name: $data['ns_template_name'] ?? null,
            owner_handle: $data['owner_handle'] ?? null,
            remove_nses: $data['remove_nses'] ?? null,
            reseller_handle: $data['reseller_handle'] ?? null,
            reset_auth_code: $data['reset_auth_code'] ?? null,
            scheduled_at: $data['scheduled_at'] ?? null,
            tech_handle: $data['tech_handle'] ?? null,
            use_domicile: $data['use_domicile'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'accept_update_fee' => $this->accept_update_fee,
            'additional_data' => $this->additional_data?->toArray(),
            'admin_handle' => $this->admin_handle,
            'auth_code' => $this->auth_code,
            'autorenew' => $this->autorenew,
            'billing_handle' => $this->billing_handle,
            'comments' => $this->comments,
            'dnssec_keys' => $this->dnssec_keys !== null ? array_map(fn(DnssecKey $item) => $item->toArray(), $this->dnssec_keys) : null,
            'domain' => $this->domain?->toArray(),
            'force_registry_update' => $this->force_registry_update,
            'id' => $this->id,
            'is_dnssec_enabled' => $this->is_dnssec_enabled,
            'is_locked' => $this->is_locked,
            'is_private_whois_enabled' => $this->is_private_whois_enabled,
            'is_sectigo_dns_enabled' => $this->is_sectigo_dns_enabled,
            'is_spamexperts_enabled' => $this->is_spamexperts_enabled,
            'name_servers' => $this->name_servers !== null ? array_map(fn(Nameserver $item) => $item->toArray(), $this->name_servers) : null,
            'ns_group' => $this->ns_group,
            'ns_template_id' => $this->ns_template_id,
            'ns_template_name' => $this->ns_template_name,
            'owner_handle' => $this->owner_handle,
            'remove_nses' => $this->remove_nses,
            'reseller_handle' => $this->reseller_handle,
            'reset_auth_code' => $this->reset_auth_code,
            'scheduled_at' => $this->scheduled_at,
            'tech_handle' => $this->tech_handle,
            'use_domicile' => $this->use_domicile,
        ], fn($v) => $v !== null);
    }
}