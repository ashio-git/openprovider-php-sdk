<?php

declare(strict_types=1);

namespace Ashio\Model;

class DomainTradeRequest
{
    /** @var array<string,mixed>|null */
    public ?array $additionalData = null;
    public ?string $adminHandle = null;
    public ?string $authCode = null;
    public ?string $autorenew = null;
    public ?string $billingHandle = null;
    public ?string $comments = null;
    /** @var DomainDnssecKey[]|null */
    public ?array $dnssecKeys = null;
    public DomainName $domain;
    /** @var Nameserver[]|null */
    public ?array $nameServers = null;
    public ?string $nsGroup = null;
    public ?int $nsTemplateId = null;
    public ?string $nsTemplateName = null;
    public ?string $ownerHandle = null;
    public ?int $period = null;
    public ?string $promoCode = null;
    public ?string $techHandle = null;
    public ?bool $useDomicile = null;

    public function __construct(DomainName $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return array<string,mixed>
     */
    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        $data = [
            'additional_data' => $this->additionalData,
            'admin_handle' => $this->adminHandle,
            'auth_code' => $this->authCode,
            'autorenew' => $this->autorenew,
            'billing_handle' => $this->billingHandle,
            'comments' => $this->comments,
            'domain' => $this->domain->toArray(),
            'ns_group' => $this->nsGroup,
            'ns_template_id' => $this->nsTemplateId,
            'ns_template_name' => $this->nsTemplateName,
            'owner_handle' => $this->ownerHandle,
            'period' => $this->period,
            'promo_code' => $this->promoCode,
            'tech_handle' => $this->techHandle,
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
                ], static fn ($v) => $v !== null);
            }, $this->dnssecKeys);
        }
        if ($this->nameServers) {
            $data['name_servers'] = array_map(static fn (Nameserver $ns) => $ns->toArray(), $this->nameServers);
        }

        return array_filter($data, static fn ($v) => $v !== null);
    }
}
