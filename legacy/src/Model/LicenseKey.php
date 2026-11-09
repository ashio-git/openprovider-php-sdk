<?php

declare(strict_types=1);

namespace Ashio\Model;

class LicenseKey
{
    public function __construct(
        public readonly ?string $key = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'key' => $this->key,
        ], fn ($v) => $v !== null);
    }
}
