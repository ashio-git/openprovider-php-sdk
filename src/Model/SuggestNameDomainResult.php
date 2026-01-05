<?php

declare(strict_types=1);

namespace Ashio\Model;

class SuggestNameDomainResult
{
    public function __construct(
        public readonly ?string $domain = null,
        public readonly ?string $name = null,
        public readonly ?string $tld = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            domain: $data['domain'] ?? null,
            name: $data['name'] ?? null,
            tld: $data['tld'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'domain' => $this->domain,
            'name' => $this->name,
            'tld' => $this->tld,
        ], fn($v) => $v !== null);
    }
}