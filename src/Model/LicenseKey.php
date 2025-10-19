<?php

declare(strict_types=1);

namespace Ashio\Model;

class LicenseKey
{
    public ?string $key = null;

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
        $this->key = $data['key'] ?? null;

        return $this;
    }
}
