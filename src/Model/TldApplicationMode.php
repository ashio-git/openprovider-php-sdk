<?php

declare(strict_types=1);

namespace Ashio\Model;

class TldApplicationMode
{
    public ?string $name = null;
    public ?string $title = null;

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
        $this->name = $data['name'] ?? null;
        $this->title = $data['title'] ?? null;

        return $this;
    }
}
