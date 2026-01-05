<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslStatistics
{
    public function __construct(
        public readonly ?SslStatisticsByStatus $by_status = null,
        public readonly ?int $total = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            by_status: isset($data['by_status']) ? SslStatisticsByStatus::fromArray($data['by_status']) : null,
            total: $data['total'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'by_status' => $this->by_status?->toArray(),
            'total' => $this->total,
        ], fn($v) => $v !== null);
    }
}