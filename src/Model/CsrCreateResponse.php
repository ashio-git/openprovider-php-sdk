<?php

declare(strict_types=1);

namespace Ashio\Model;

class CsrCreateResponse
{
    public ?string $command = null;
    public ?string $config = null;
    public ?string $config_path = null;
    public ?string $csr = null;
    public ?string $key = null;
    public ?string $key_path = null;

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
        $this->command = $data['command'] ?? null;
        $this->config = $data['config'] ?? null;
        $this->config_path = $data['config_path'] ?? null;
        $this->csr = $data['csr'] ?? null;
        $this->key = $data['key'] ?? null;
        $this->key_path = $data['key_path'] ?? null;

        return $this;
    }
}
