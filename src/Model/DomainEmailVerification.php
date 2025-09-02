<?php

declare(strict_types=1);

namespace Ashio\Model;

class DomainEmailVerification
{
    public ?string $description = null;
    public ?string $domain = null;
    public ?string $email = null;
    public ?string $expirationDate = null;
    public ?string $handle = null;
    public ?bool $isSuspended = null;
    public ?string $status = null;

    public function fromArray(array $data): self
    {
        $this->description = $data['description'] ?? null;
        $this->domain = $data['domain'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->expirationDate = $data['expiration_date'] ?? null;
        $this->handle = $data['handle'] ?? null;
        $this->isSuspended = $data['is_suspended'] ?? null;
        $this->status = $data['status'] ?? null;
        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'description' => $this->description,
            'domain' => $this->domain,
            'email' => $this->email,
            'expiration_date' => $this->expirationDate,
            'handle' => $this->handle,
            'is_suspended' => $this->isSuspended,
            'status' => $this->status,
        ], function ($value) {
            return $value !== null;
        });
    }
}
