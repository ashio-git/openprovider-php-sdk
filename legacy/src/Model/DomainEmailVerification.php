<?php

declare(strict_types=1);

namespace Ashio\Model;

class DomainEmailVerification
{
    public function __construct(
        public readonly ?string $description = null,
        public readonly ?string $domain = null,
        public readonly ?string $email = null,
        public readonly ?string $expiration_date = null,
        public readonly ?string $handle = null,
        public readonly ?bool $is_suspended = null,
        public readonly ?string $status = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            description: $data['description'] ?? null,
            domain: $data['domain'] ?? null,
            email: $data['email'] ?? null,
            expiration_date: $data['expiration_date'] ?? null,
            handle: $data['handle'] ?? null,
            is_suspended: $data['is_suspended'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'description' => $this->description,
            'domain' => $this->domain,
            'email' => $this->email,
            'expiration_date' => $this->expiration_date,
            'handle' => $this->handle,
            'is_suspended' => $this->is_suspended,
            'status' => $this->status,
        ], fn ($v) => $v !== null);
    }
}
