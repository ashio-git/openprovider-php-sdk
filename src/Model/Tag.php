<?php

declare(strict_types=1);

namespace Ashio\Model;

class Tag
{
    public ?string $key = null;
    public ?string $value = null;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->key = $data['key'] ?? null;
        $this->value = $data['value'] ?? null;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'key' => $this->key,
            'value' => $this->value,
        ], function ($value) {
            return $value !== null;
        });
    }
}
