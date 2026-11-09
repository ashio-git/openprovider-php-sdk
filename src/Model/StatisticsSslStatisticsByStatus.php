<?php

declare(strict_types=1);

namespace Ashio\Model;

class StatisticsSslStatisticsByStatus
{
    public ?int $ACT = null;
    public ?int $FAI = null;
    public ?int $NSR = null;
    public ?int $PEN = null;
    public ?int $REJ = null;
    public ?int $REQ = null;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->ACT = $data['ACT'] ?? null;
        $this->FAI = $data['FAI'] ?? null;
        $this->NSR = $data['NSR'] ?? null;
        $this->PEN = $data['PEN'] ?? null;
        $this->REJ = $data['REJ'] ?? null;
        $this->REQ = $data['REQ'] ?? null;

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
