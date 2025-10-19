<?php

declare(strict_types=1);

namespace Ashio\Model;

class DomainUpdateRequest
{
    public ?string $autorenew = null;
    public ?string $ns_group = null;
    /** @var Nameserver[]|null */
    public ?array $name_servers = null;
    /** @var array<string,mixed>|null */
    public ?array $additional_data = null;

    /**
     * @return array<string,mixed>
     */
    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        $data = [];
        if ($this->autorenew !== null) {
            $data['autorenew'] = $this->autorenew;
        }
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
