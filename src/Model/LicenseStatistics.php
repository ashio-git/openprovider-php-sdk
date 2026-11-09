<?php

declare(strict_types=1);

namespace Ashio\Model;

class LicenseStatistics
{
    public function __construct(
        public readonly ?int $total = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: $data['total'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'total' => $this->total,
        ], fn ($v) => $v !== null);
    }
}
