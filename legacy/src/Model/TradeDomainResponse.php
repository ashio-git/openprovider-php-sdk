<?php

namespace Ashio\Model;

class TradeDomainResponse
{
    /**
     * @var string|null
     */
    public $creation_date;

    /**
     * @var string|null
     */
    public $expiration_date;

    /**
     * @var string|null
     */
    public $renewal_date;

    /**
     * @var string|null
     */
    public $status;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->creation_date = $data['creation_date'] ?? null;
        $this->expiration_date = $data['expiration_date'] ?? null;
        $this->renewal_date = $data['renewal_date'] ?? null;
        $this->status = $data['status'] ?? null;

        return $this;
    }
}
