<?php

declare(strict_types=1);

namespace Ashio\Model;

class PromotionData
{
    public function __construct(
        public readonly ?string $end_date = null,
        public readonly ?string $start_date = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            end_date: $data['end_date'] ?? null,
            start_date: $data['start_date'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'end_date' => $this->end_date,
            'start_date' => $this->start_date,
        ], fn ($v) => $v !== null);
    }
}
