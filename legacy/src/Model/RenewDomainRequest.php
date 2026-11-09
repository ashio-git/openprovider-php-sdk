<?php

declare(strict_types=1);

namespace Ashio\Model;

class RenewDomainRequest
{
    public function __construct(
        public readonly ?DomainName $domain = null,
        public readonly ?int $id = null,
        public readonly ?int $period = null,
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
            period: $data['period'] ?? null,
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
            'period' => $this->period,
        ], fn ($v) => $v !== null);
    }
}
