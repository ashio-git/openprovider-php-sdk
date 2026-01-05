<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslOrderDomainValidationMethods
{
    public function __construct(
        public readonly ?string $host_name = null,
        public readonly ?string $method = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            host_name: $data['host_name'] ?? null,
            method: $data['method'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'host_name' => $this->host_name,
            'method' => $this->method,
        ], fn($v) => $v !== null);
    }
}