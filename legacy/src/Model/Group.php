<?php

declare(strict_types=1);

namespace Ashio\Model;

class Group
{
    public function __construct(
        public readonly ?int $domain_count = null,
        public readonly ?int $id = null,
        /** @var NameserverSet[]|null */
        public readonly ?array $name_servers = null,
        public readonly ?int $ns_count = null,
        public readonly ?string $ns_group = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            domain_count: $data['domain_count'] ?? null,
            id: $data['id'] ?? null,
            name_servers: isset($data['name_servers']) ? array_map(fn (array $item) => (new NameserverSet())->fromArray($item), $data['name_servers']) : null,
            ns_count: $data['ns_count'] ?? null,
            ns_group: $data['ns_group'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'domain_count' => $this->domain_count,
            'id' => $this->id,
            'name_servers' => $this->name_servers !== null ? array_map(fn (NameserverSet $item) => $item->toArray(), $this->name_servers) : null,
            'ns_count' => $this->ns_count,
            'ns_group' => $this->ns_group,
        ], fn ($v) => $v !== null);
    }
}
