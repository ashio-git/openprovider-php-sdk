<?php

declare(strict_types=1);

namespace Ashio\Model;

class CsrPublicKey
{
    public ?int $bits = null;
    public ?string $key = null;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->bits = $data['bits'] ?? null;
        $this->key = $data['key'] ?? null;

        return $this;
    }
}
