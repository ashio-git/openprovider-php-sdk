<?php

declare(strict_types=1);

namespace Ashio\Model;

class StatisticsLicenseStatistics
{
    public ?int $total = null;

    public function fromArray(array $data): self
    {
        $this->total = $data['total'] ?? null;
        return $this;
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this));
    }
}
