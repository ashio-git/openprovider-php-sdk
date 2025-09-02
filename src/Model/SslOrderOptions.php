<?php

declare(strict_types=1);

namespace Vexura\Model;

class SslOrderOptions
{
    public ?string $features = null;

    /**
     * @param array<string,mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->features = $data['features'] ?? null;
        return $this;
    }
}

