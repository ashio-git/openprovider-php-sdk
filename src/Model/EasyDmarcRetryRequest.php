<?php

declare(strict_types=1);

namespace Ashio\Model;

class EasyDmarcRetryRequest
{
    public ?int $id = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
        ], static fn($v) => $v !== null);
    }
}
