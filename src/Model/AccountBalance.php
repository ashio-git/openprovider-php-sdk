<?php

declare(strict_types=1);

namespace Ashio\Model;

class AccountBalance
{
    public ?float $balance = null;
    public ?float $reserved_balance = null;

    /**
     * @param array<string,mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->balance = isset($data['balance']) ? (float)$data['balance'] : null;
        $this->reserved_balance = isset($data['reserved_balance']) ? (float)$data['reserved_balance'] : null;
        return $this;
    }
}

