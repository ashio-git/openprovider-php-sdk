<?php

declare(strict_types=1);

namespace Ashio\Model;

class ActionResponse
{
    public ?bool $success = null;

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
        $this->success = $data['success'] ?? null;

        return $this;
    }
}
