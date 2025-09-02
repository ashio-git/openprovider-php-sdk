<?php

declare(strict_types=1);

namespace Vexura\Model;

class StatisticsDomainStatistics
{
    public ?StatisticsDomainStatisticsByStatus $byStatus = null;
    public ?int $total = null;

    public function fromArray(array $data): self
    {
        if (isset($data['by_status'])) {
            $this->byStatus = (new StatisticsDomainStatisticsByStatus())->fromArray($data['by_status']);
        }
        $this->total = $data['total'] ?? null;
        return $this;
    }

    public function toArray(): array
    {
        $data = [
            'total' => $this->total,
        ];
        if ($this->byStatus) {
            $data['by_status'] = $this->byStatus->toArray();
        }
        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }
}
