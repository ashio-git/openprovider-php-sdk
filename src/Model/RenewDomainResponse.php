<?php

namespace Ashio\Model;

class RenewDomainResponse
{
    /**
     * @var string|null
     */
    public $status;

    public function fromArray(array $data): self
    {
        $this->status = $data['status'] ?? null;
        return $this;
    }
}