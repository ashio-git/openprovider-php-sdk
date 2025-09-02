<?php

declare(strict_types=1);

namespace Vexura\Model;

class SslActionResponse
{
    public ?int $id = null;
    public ?bool $success = null;

    /**
     * @param array<string,mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->success = $data['success'] ?? null;
        return $this;
    }
}

