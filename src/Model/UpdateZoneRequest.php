<?php

declare(strict_types=1);

namespace Ashio\Model;

class UpdateZoneRequest
{
    public function __construct(
        public readonly ?DomainName $domain = null,
        public readonly ?int $id = null,
        public readonly ?bool $is_spamexperts_enabled = null,
        public readonly ?string $master_ip = null,
        public readonly ?string $name = null,
        public readonly ?PremiumDnsData $premium_dns = null,
        public readonly ?string $provider = null,
        public readonly ?RecordUpdates $records = null,
        public readonly ?string $type = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            domain: isset($data['domain']) ? (new DomainName())->fromArray($data['domain']) : null,
            id: $data['id'] ?? null,
            is_spamexperts_enabled: $data['is_spamexperts_enabled'] ?? null,
            master_ip: $data['master_ip'] ?? null,
            name: $data['name'] ?? null,
            premium_dns: isset($data['premium_dns']) ? PremiumDnsData::fromArray($data['premium_dns']) : null,
            provider: $data['provider'] ?? null,
            records: isset($data['records']) ? RecordUpdates::fromArray($data['records']) : null,
            type: $data['type'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'domain' => $this->domain?->toArray(),
            'id' => $this->id,
            'is_spamexperts_enabled' => $this->is_spamexperts_enabled,
            'master_ip' => $this->master_ip,
            'name' => $this->name,
            'premium_dns' => $this->premium_dns?->toArray(),
            'provider' => $this->provider,
            'records' => $this->records?->toArray(),
            'type' => $this->type,
        ], fn ($v) => $v !== null);
    }
}
