<?php

declare(strict_types=1);

namespace Ashio\Model;

class DomainName
{
    public ?string $name = null;
    public ?string $extension = null;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->name = $data['name'] ?? null;
        $this->extension = $data['extension'] ?? null;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'extension' => $this->extension,
        ], static fn ($v) => $v !== null);
    }
}
