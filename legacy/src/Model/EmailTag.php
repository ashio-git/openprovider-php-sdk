<?php

namespace Ashio\Model;

class EmailTag
{
    /**
     * @var string|null
     */
    public $key;

    /**
     * @var string|null
     */
    public $value;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->key = $data['key'] ?? null;
        $this->value = $data['value'] ?? null;

        return $this;
    }
}
