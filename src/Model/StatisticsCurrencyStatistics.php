<?php

declare(strict_types=1);

namespace Ashio\Model;

class StatisticsCurrencyStatistics
{
    public ?string $modified = null;

    public function fromArray(array $data): self
    {
        $this->modified = $data['modified'] ?? null;
        return $this;
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this));
    }
}
