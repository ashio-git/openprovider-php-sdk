<?php

declare(strict_types=1);

namespace Ashio\Model;

class StatisticsCustomerStatistics
{
    public ?string $modified = null;
    public ?int $total = null;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->modified = $data['modified'] ?? null;
        $this->total = $data['total'] ?? null;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter(get_object_vars($this), static fn ($v) => $v !== null);
    }
}
