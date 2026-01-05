<?php

declare(strict_types=1);

namespace Ashio\Model;

class LicenseKey
{
    public function __construct(
        public readonly ?string $key = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'key' => $this->key,
        ], fn($v) => $v !== null);
    }
}