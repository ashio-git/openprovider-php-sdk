<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslOrderDomainValidationStatuses
{
    public ?string $ca_operation = null;
    public ?string $ca_order_status = null;
    public ?string $ca_status = null;
    public ?string $status = null;

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
        $this->ca_operation = $data['ca_operation'] ?? null;
        $this->ca_order_status = $data['ca_order_status'] ?? null;
        $this->ca_status = $data['ca_status'] ?? null;
        $this->status = $data['status'] ?? null;

        return $this;
    }
}
