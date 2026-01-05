<?php

declare(strict_types=1);

namespace Ashio\Model;

class CurrencyStatistics
{
    public function __construct(
        public readonly ?string $modified = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modified: $data['modified'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'modified' => $this->modified,
        ], fn($v) => $v !== null);
    }
}