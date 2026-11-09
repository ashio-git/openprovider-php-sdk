<?php

declare(strict_types=1);

namespace Ashio\Model;

class TryAgainLastOperationRequest
{
    public function __construct(
        public readonly ?string $auth_code = null,
        public readonly ?DomainName $domain = null,
        public readonly ?int $id = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            auth_code: $data['auth_code'] ?? null,
            domain: isset($data['domain']) ? (new DomainName())->fromArray($data['domain']) : null,
            id: $data['id'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'auth_code' => $this->auth_code,
            'domain' => $this->domain?->toArray(),
            'id' => $this->id,
        ], fn ($v) => $v !== null);
    }
}
