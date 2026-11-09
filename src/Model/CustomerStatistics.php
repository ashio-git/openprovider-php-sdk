<?php

declare(strict_types=1);

namespace Ashio\Model;

class CustomerStatistics
{
    public function __construct(
        public readonly ?string $modified = null,
        public readonly ?int $total = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            modified: $data['modified'] ?? null,
            total: $data['total'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'modified' => $this->modified,
            'total' => $this->total,
        ], fn ($v) => $v !== null);
    }
}
