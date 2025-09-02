<?php

namespace Ashio\Model;

class RenewDomainRequest
{
    public ?int $period = null;

    public function __construct(?int $period = null)
    {
        $this->period = $period;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'period' => $this->period,
        ], static fn($v) => $v !== null);
    }
}
