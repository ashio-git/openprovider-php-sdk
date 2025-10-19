<?php

namespace Ashio\Model;

class AuthInfo
{
    /**
     * @var string|null
     */
    public $pw;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->pw = $data['pw'] ?? null;

        return $this;
    }
}
