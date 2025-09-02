<?php

declare(strict_types=1);

namespace Vexura\Model;

class EasyDmarcCreateRequest
{
    public DomainName $domain;
    public string $ownerHandle;

    public function __construct(DomainName $domain, string $ownerHandle)
    {
        $this->domain = $domain;
        $this->ownerHandle = $ownerHandle;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'domain' => $this->domain->toArray(),
            'owner_handle' => $this->ownerHandle,
        ];
    }
}
