<?php

namespace Ashio\Model;

class TldLevelPrice
{
    public ?int $level = null;
    public ?TldPrice $price = null;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->level = $data['level'] ?? null;
        $this->price = isset($data['price']) ? new TldPrice($data['price']) : null;

        return $this;
    }
}
