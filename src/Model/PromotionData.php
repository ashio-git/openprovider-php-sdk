<?php

namespace Ashio\Model;

class PromotionData
{
    /**
     * @var string|null
     */
    public $start_date;

    /**
     * @var string|null
     */
    public $end_date;

    public function fromArray(array $data): self
    {
        $this->start_date = $data['start_date'] ?? null;
        $this->end_date = $data['end_date'] ?? null;
        return $this;
    }
}