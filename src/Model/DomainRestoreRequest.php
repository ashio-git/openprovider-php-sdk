<?php

declare(strict_types=1);

namespace Vexura\Model;

class DomainRestoreRequest
{
    public ?DomainName $domain = null;
    public ?int $id = null;

    public function __construct(?DomainName $domain = null, ?int $id = null)
    {
        $this->domain = $domain;
        $this->id = $id;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'domain' => $this->domain?->toArray(),
            'id' => $this->id,
        ], static fn($v) => $v !== null);
    }
}
