<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslOrderDomainValidationMethods
{
    public ?string $host_name = null;
    public ?string $method = null;

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
        $this->host_name = $data['host_name'] ?? null;
        $this->method = $data['method'] ?? null;

        return $this;
    }
}
