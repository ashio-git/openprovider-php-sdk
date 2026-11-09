<?php

declare(strict_types=1);

namespace Ashio\Model;

class CreateZoneRequest
{
    public function __construct(
        public readonly ?DomainName $domain = null,
        public readonly ?string $is_spamexperts_enabled = null,
        public readonly ?string $master_ip = null,
        public readonly ?string $provider = null,
        /** @var Record[]|null */
        public readonly ?array $records = null,
        public readonly ?bool $secured = null,
        public readonly ?string $template_name = null,
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
            is_spamexperts_enabled: $data['is_spamexperts_enabled'] ?? null,
            master_ip: $data['master_ip'] ?? null,
            provider: $data['provider'] ?? null,
            records: isset($data['records']) ? array_map(fn (array $item) => Record::fromArray($item), $data['records']) : null,
            secured: $data['secured'] ?? null,
            template_name: $data['template_name'] ?? null,
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
            'is_spamexperts_enabled' => $this->is_spamexperts_enabled,
            'master_ip' => $this->master_ip,
            'provider' => $this->provider,
            'records' => $this->records !== null ? array_map(fn (Record $item) => $item->toArray(), $this->records) : null,
            'secured' => $this->secured,
            'template_name' => $this->template_name,
            'type' => $this->type,
        ], fn ($v) => $v !== null);
    }
}
