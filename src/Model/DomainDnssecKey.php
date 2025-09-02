<?php

declare(strict_types=1);

namespace Ashio\Model;

class DomainDnssecKey
{
    public ?int $alg = null;
    public ?int $flags = null;
    public ?int $protocol = null;
    public ?string $pub_key = null;
    public ?int $readonly = null;

    /**
     * @param array<string,mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->alg = $data['alg'] ?? null;
        $this->flags = $data['flags'] ?? null;
        $this->protocol = $data['protocol'] ?? null;
        $this->pub_key = $data['pub_key'] ?? null;
        $this->readonly = $data['readonly'] ?? null;
        return $this;
    }
}

