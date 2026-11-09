<?php

declare(strict_types=1);

namespace Ashio\Model;

class ApproveTransferRequest
{
    public function __construct(
        public readonly ?int $approve = null,
        public readonly ?string $auth_code = null,
        public readonly ?DomainName $domain = null,
        public readonly ?int $id = null,
        public readonly ?string $registrar_tag = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            approve: $data['approve'] ?? null,
            auth_code: $data['auth_code'] ?? null,
            domain: isset($data['domain']) ? (new DomainName())->fromArray($data['domain']) : null,
            id: $data['id'] ?? null,
            registrar_tag: $data['registrar_tag'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'approve' => $this->approve,
            'auth_code' => $this->auth_code,
            'domain' => $this->domain?->toArray(),
            'id' => $this->id,
            'registrar_tag' => $this->registrar_tag,
        ], fn ($v) => $v !== null);
    }
}
