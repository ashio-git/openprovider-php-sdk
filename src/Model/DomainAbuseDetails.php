<?php

declare(strict_types=1);

namespace Ashio\Model;

class DomainAbuseDetails
{
    public ?int $abuse_id = null;
    public ?bool $is_domain_held = null;
    public ?string $message = null;

    /**
     * @param array<string,mixed> $data
     * @return self
     */
    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->abuse_id = $data['abuse_id'] ?? null;
        $this->is_domain_held = $data['is_domain_held'] ?? null;
        $this->message = $data['message'] ?? null;

        return $this;
    }
}
