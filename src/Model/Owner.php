<?php

declare(strict_types=1);

namespace Ashio\Model;

class Owner
{
    public function __construct(
        public readonly ?string $company_name = null,
        public readonly ?string $full_name = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            company_name: $data['company_name'] ?? null,
            full_name: $data['full_name'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'company_name' => $this->company_name,
            'full_name' => $this->full_name,
        ], fn($v) => $v !== null);
    }
}