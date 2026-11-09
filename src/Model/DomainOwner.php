<?php

declare(strict_types=1);

namespace Ashio\Model;

class DomainOwner
{
    public ?string $company_name = null;
    public ?string $full_name = null;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->company_name = $data['company_name'] ?? null;
        $this->full_name = $data['full_name'] ?? null;

        return $this;
    }
}
