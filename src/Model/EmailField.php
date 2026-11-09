<?php

namespace Ashio\Model;

class EmailField
{
    /**
     * @var string|null
     */
    public $name;

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
        $this->name = $data['name'] ?? null;
        $this->value = $data['value'] ?? null;

        return $this;
    }
}
