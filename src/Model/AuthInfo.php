<?php

namespace Vexura\Model;

class AuthInfo
{
    /**
     * @var string|null
     */
    public $pw;

    public function fromArray(array $data): self
    {
        $this->pw = $data['pw'] ?? null;
        return $this;
    }
}