<?php

declare(strict_types=1);

namespace Ashio\Model;

class CreateDomainRequest
{
    public function __construct(
        public readonly ?float $accept_eap_fee = null,
        public readonly ?float $accept_premium_fee = null,
        public readonly ?AdditionalData $additional_data = null,
        public readonly ?string $admin_handle = null,
        public readonly ?string $application_mode = null,
        public readonly ?string $application_notice_id = null,
        public readonly ?string $application_smd = null,
        public readonly ?string $auth_code = null,
        public readonly ?string $autorenew = null,
        public readonly ?string $billing_handle = null,
        public readonly ?string $comments = null,
        /** @var DnssecKey[]|null */
        public readonly ?array $dnssec_keys = null,
        public readonly ?Domain $domain = null,
        public readonly ?bool $is_dnssec_enabled = null,
        public readonly ?bool $is_easy_dmarc_enabled = null,
        public readonly ?bool $is_private_whois_enabled = null,
        public readonly ?bool $is_sectigo_dns_enabled = null,
        public readonly ?bool $is_spamexperts_enabled = null,
        /** @var Nameserver[]|null */
        public readonly ?array $name_servers = null,
        public readonly ?string $ns_group = null,
        public readonly ?int $ns_template_id = null,
        public readonly ?string $ns_template_name = null,
        public readonly ?string $owner_handle = null,
        public readonly ?int $period = null,
        public readonly ?string $promo_code = null,
        public readonly ?string $provider = null,
        public readonly ?string $reseller_handle = null,
        public readonly ?string $tech_handle = null,
        public readonly ?string $unit = null,
        public readonly ?bool $use_domicile = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            accept_eap_fee: $data['accept_eap_fee'] ?? null,
            accept_premium_fee: $data['accept_premium_fee'] ?? null,
            additional_data: isset($data['additional_data']) ? AdditionalData::fromArray($data['additional_data']) : null,
            admin_handle: $data['admin_handle'] ?? null,
            application_mode: $data['application_mode'] ?? null,
            application_notice_id: $data['application_notice_id'] ?? null,
            application_smd: $data['application_smd'] ?? null,
            auth_code: $data['auth_code'] ?? null,
            autorenew: $data['autorenew'] ?? null,
            billing_handle: $data['billing_handle'] ?? null,
            comments: $data['comments'] ?? null,
            dnssec_keys: isset($data['dnssec_keys']) ? array_map(fn(array $item) => DnssecKey::fromArray($item), $data['dnssec_keys']) : null,
            domain: isset($data['domain']) ? Domain::fromArray($data['domain']) : null,
            is_dnssec_enabled: $data['is_dnssec_enabled'] ?? null,
            is_easy_dmarc_enabled: $data['is_easy_dmarc_enabled'] ?? null,
            is_private_whois_enabled: $data['is_private_whois_enabled'] ?? null,
            is_sectigo_dns_enabled: $data['is_sectigo_dns_enabled'] ?? null,
            is_spamexperts_enabled: $data['is_spamexperts_enabled'] ?? null,
            name_servers: isset($data['name_servers']) ? array_map(fn(array $item) => Nameserver::fromArray($item), $data['name_servers']) : null,
            ns_group: $data['ns_group'] ?? null,
            ns_template_id: $data['ns_template_id'] ?? null,
            ns_template_name: $data['ns_template_name'] ?? null,
            owner_handle: $data['owner_handle'] ?? null,
            period: $data['period'] ?? null,
            promo_code: $data['promo_code'] ?? null,
            provider: $data['provider'] ?? null,
            reseller_handle: $data['reseller_handle'] ?? null,
            tech_handle: $data['tech_handle'] ?? null,
            unit: $data['unit'] ?? null,
            use_domicile: $data['use_domicile'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'accept_eap_fee' => $this->accept_eap_fee,
            'accept_premium_fee' => $this->accept_premium_fee,
            'additional_data' => $this->additional_data?->toArray(),
            'admin_handle' => $this->admin_handle,
            'application_mode' => $this->application_mode,
            'application_notice_id' => $this->application_notice_id,
            'application_smd' => $this->application_smd,
            'auth_code' => $this->auth_code,
            'autorenew' => $this->autorenew,
            'billing_handle' => $this->billing_handle,
            'comments' => $this->comments,
            'dnssec_keys' => $this->dnssec_keys !== null ? array_map(fn(DnssecKey $item) => $item->toArray(), $this->dnssec_keys) : null,
            'domain' => $this->domain?->toArray(),
            'is_dnssec_enabled' => $this->is_dnssec_enabled,
            'is_easy_dmarc_enabled' => $this->is_easy_dmarc_enabled,
            'is_private_whois_enabled' => $this->is_private_whois_enabled,
            'is_sectigo_dns_enabled' => $this->is_sectigo_dns_enabled,
            'is_spamexperts_enabled' => $this->is_spamexperts_enabled,
            'name_servers' => $this->name_servers !== null ? array_map(fn(Nameserver $item) => $item->toArray(), $this->name_servers) : null,
            'ns_group' => $this->ns_group,
            'ns_template_id' => $this->ns_template_id,
            'ns_template_name' => $this->ns_template_name,
            'owner_handle' => $this->owner_handle,
            'period' => $this->period,
            'promo_code' => $this->promo_code,
            'provider' => $this->provider,
            'reseller_handle' => $this->reseller_handle,
            'tech_handle' => $this->tech_handle,
            'unit' => $this->unit,
            'use_domicile' => $this->use_domicile,
        ], fn($v) => $v !== null);
    }
}