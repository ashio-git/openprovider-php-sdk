<?php

namespace Ashio\Model;

class AdditionalDataSelectOption
{
    /**
     * @var string|null
     */
    public $value;

    /**
     * @var string|null
     */
    public $description;

    public function fromArray(array $data): self
    {
        $this->value = $data['value'] ?? null;
        $this->description = $data['description'] ?? null;
        return $this;
    }
}