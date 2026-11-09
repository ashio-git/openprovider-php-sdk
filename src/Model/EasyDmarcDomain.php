<?php

namespace Ashio\Model;

class EasyDmarcDomain
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $extension;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->name = $data['name'] ?? null;
        $this->extension = $data['extension'] ?? null;

        return $this;
    }
}
