<?php

namespace Vexura\Model;

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

    public function fromArray(array $data): self
    {
        $this->name = $data['name'] ?? null;
        $this->extension = $data['extension'] ?? null;
        return $this;
    }
}