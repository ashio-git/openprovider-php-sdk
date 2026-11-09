<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslOrderDomainValidationStatuses
{
    public function __construct(
        public readonly ?string $ca_operation = null,
        public readonly ?string $ca_order_status = null,
        public readonly ?string $ca_status = null,
        public readonly ?string $status = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ca_operation: $data['ca_operation'] ?? null,
            ca_order_status: $data['ca_order_status'] ?? null,
            ca_status: $data['ca_status'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'ca_operation' => $this->ca_operation,
            'ca_order_status' => $this->ca_order_status,
            'ca_status' => $this->ca_status,
            'status' => $this->status,
        ], fn ($v) => $v !== null);
    }
}
