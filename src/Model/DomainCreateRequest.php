<?php

declare(strict_types=1);

namespace Ashio\Model;

class DomainCreateRequest
{
    public string $owner_handle;
    public DomainName $domain;
    public int $period = 1;
    public ?string $ns_group = null;
    /** @var Nameserver[]|null */
    public ?array $name_servers = null;
    /** @var array<string,mixed>|null */
    public ?array $additional_data = null;

    public function __construct(string $ownerHandle, DomainName $domain, int $period = 1)
    {
        $this->owner_handle = $ownerHandle;
        $this->domain = $domain;
        $this->period = $period;
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
            'owner_handle' => $this->owner_handle,
            'domain' => $this->domain->toArray(),
            'period' => $this->period,
        ];
        if ($this->ns_group !== null) {
            $data['ns_group'] = $this->ns_group;
        }
        if ($this->name_servers) {
            $data['name_servers'] = array_map(static fn (Nameserver $ns) => $ns->toArray(), $this->name_servers);
        }
        if ($this->additional_data) {
            $data['additional_data'] = $this->additional_data;
        }

        return $data;
    }
}
