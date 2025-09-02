<?php

namespace Ashio\Model;

class DomainWhoisPrivacyData
{
    public ?string $expiration_date = null;

    public function __construct(array $data = [])
    {
        if ($data) {
            $this->fromArray($data);
        }
    }

    /**
     * @param array<string,mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->expiration_date = $data['expiration_date'] ?? null;
        return $this;
    }
}
