<?php

declare(strict_types=1);

namespace Ashio\Model;

class ResetAuthCodeRequest
{
    public function __construct(
        public readonly ?string $auth_code_type = null,
        public readonly ?DomainName $domain = null,
        public readonly ?int $id = null,
        public readonly ?string $sending_type = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            auth_code_type: $data['auth_code_type'] ?? null,
            domain: isset($data['domain']) ? (new DomainName())->fromArray($data['domain']) : null,
            id: $data['id'] ?? null,
            sending_type: $data['sending_type'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'auth_code_type' => $this->auth_code_type,
            'domain' => $this->domain?->toArray(),
            'id' => $this->id,
            'sending_type' => $this->sending_type,
        ], fn ($v) => $v !== null);
    }
}
