<?php

declare(strict_types=1);

namespace Ashio\Model;

class DomainStatistics
{
    public function __construct(
        public readonly ?DomainStatisticsByStatus $by_status = null,
        public readonly ?int $total = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            by_status: isset($data['by_status']) ? DomainStatisticsByStatus::fromArray($data['by_status']) : null,
            total: $data['total'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'by_status' => $this->by_status?->toArray(),
            'total' => $this->total,
        ], fn ($v) => $v !== null);
    }
}
