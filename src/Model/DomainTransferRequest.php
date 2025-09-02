<?php

declare(strict_types=1);

namespace Vexura\Model;

class DomainTransferRequest
{
    public ?float $acceptPremiumFee = null;
    /** @var array<string,mixed>|null */
    public ?array $additionalData = null;
    public ?string $adminHandle = null;
    public ?string $at = null;
    public ?string $authCode = null;
    public ?string $autorenew = null;
    public ?string $billingHandle = null;
    public ?string $comments = null;
    /** @var DomainDnssecKey[]|null */
    public ?array $dnssecKeys = null;
    public DomainName $domain;
    public ?bool $importContactsFromRegistry = null;
    public ?bool $importNameserversFromRegistry = null;
    public ?bool $isDnssecEnabled = null;
    public ?bool $isPrivateWhoisEnabled = null;
    /** @var Nameserver[]|null */
    public ?array $nameServers = null;
    public ?string $nsGroup = null;
    public ?int $nsTemplateId = null;
    public ?string $nsTemplateName = null;
    public ?string $ownerHandle = null;
    public ?string $promoCode = null;
    public ?string $resellerHandle = null;
    public ?string $roid = null;
    public ?string $techHandle = null;
    public ?string $unit = null;
    public ?bool $useDomicile = null;

    public function __construct(DomainName $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'accept_premium_fee' => $this->acceptPremiumFee,
            'additional_data' => $this->additionalData,
            'admin_handle' => $this->adminHandle,
            'at' => $this->at,
            'auth_code' => $this->authCode,
            'autorenew' => $this->autorenew,
            'billing_handle' => $this->billingHandle,
            'comments' => $this->comments,
            'domain' => $this->domain->toArray(),
            'import_contacts_from_registry' => $this->importContactsFromRegistry,
            'import_nameservers_from_registry' => $this->importNameserversFromRegistry,
            'is_dnssec_enabled' => $this->isDnssecEnabled,
            'is_private_whois_enabled' => $this->isPrivateWhoisEnabled,
            'ns_group' => $this->nsGroup,
            'ns_template_id' => $this->nsTemplateId,
            'ns_template_name' => $this->nsTemplateName,
            'owner_handle' => $this->ownerHandle,
            'promo_code' => $this->promoCode,
            'reseller_handle' => $this->resellerHandle,
            'roid' => $this->roid,
            'tech_handle' => $this->techHandle,
            'unit' => $this->unit,
            'use_domicile' => $this->useDomicile,
        ];
        if ($this->dnssecKeys) {
            $data['dnssec_keys'] = array_map(static function (DomainDnssecKey $key) {
                return array_filter([
                    'alg' => $key->alg,
                    'flags' => $key->flags,
                    'protocol' => $key->protocol,
                    'pub_key' => $key->pub_key,
                    'readonly' => $key->readonly,
                ], static fn($v) => $v !== null);
            }, $this->dnssecKeys);
        }
        if ($this->nameServers) {
            $data['name_servers'] = array_map(static fn(Nameserver $ns) => $ns->toArray(), $this->nameServers);
        }
        return array_filter($data, static fn($v) => $v !== null);
    }
}
