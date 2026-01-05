<?php

declare(strict_types=1);

namespace Ashio\Model;

class WhoisPrivacyData
{
    public function __construct(
        public readonly ?string $expiration_date = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            expiration_date: $data['expiration_date'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'expiration_date' => $this->expiration_date,
        ], fn($v) => $v !== null);
    }
}