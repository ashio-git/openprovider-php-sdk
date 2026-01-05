<?php

declare(strict_types=1);

namespace Ashio\Model;

class Statistics
{
    public function __construct(
        public readonly ?CurrencyStatistics $currency = null,
        public readonly ?CustomerStatistics $customer = null,
        public readonly ?DnsStatistics $dns = null,
        public readonly ?DomainStatistics $domain = null,
        public readonly ?int $level = null,
        public readonly ?LicenseStatistics $license = null,
        public readonly ?SpamExpertsStatistics $spam_experts = null,
        public readonly ?SslStatistics $ssl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            currency: isset($data['currency']) ? CurrencyStatistics::fromArray($data['currency']) : null,
            customer: isset($data['customer']) ? CustomerStatistics::fromArray($data['customer']) : null,
            dns: isset($data['dns']) ? DnsStatistics::fromArray($data['dns']) : null,
            domain: isset($data['domain']) ? DomainStatistics::fromArray($data['domain']) : null,
            level: $data['level'] ?? null,
            license: isset($data['license']) ? LicenseStatistics::fromArray($data['license']) : null,
            spam_experts: isset($data['spam_experts']) ? SpamExpertsStatistics::fromArray($data['spam_experts']) : null,
            ssl: isset($data['ssl']) ? SslStatistics::fromArray($data['ssl']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'currency' => $this->currency?->toArray(),
            'customer' => $this->customer?->toArray(),
            'dns' => $this->dns?->toArray(),
            'domain' => $this->domain?->toArray(),
            'level' => $this->level,
            'license' => $this->license?->toArray(),
            'spam_experts' => $this->spam_experts?->toArray(),
            'ssl' => $this->ssl?->toArray(),
        ], fn($v) => $v !== null);
    }
}