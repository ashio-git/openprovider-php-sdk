<?php

declare(strict_types=1);

namespace Vexura\Model;

class DomainToken
{
    public ?string $token = null;
    public ?string $url = null;

    public function fromArray(array $data): self
    {
        $this->token = $data['token'] ?? null;
        $this->url = $data['url'] ?? null;
        return $this;
    }
}
