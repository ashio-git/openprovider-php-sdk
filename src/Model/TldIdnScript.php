<?php

declare(strict_types=1);

namespace Ashio\Model;

class TldIdnScript
{
    public ?string $description = null;
    public ?string $value = null;

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
        $this->description = $data['description'] ?? null;
        $this->value = $data['value'] ?? null;

        return $this;
    }
}
