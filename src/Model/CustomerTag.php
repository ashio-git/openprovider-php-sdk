<?php

declare(strict_types=1);

namespace Ashio\Model;

class CustomerTag
{
    /**
     * @var string|null The key of the tag. Allowed values: 'customer'.
     */
    public ?string $key = null;

    /**
     * @var string|null The value of the tag.
     */
    public ?string $value = null;

    /**
     * Populates the CustomerTag object from an array.
     *
     * @param array<string, mixed> $data The data to populate from.
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->key = $data['key'] ?? null;
        $this->value = $data['value'] ?? null;
        return $this;
    }

    /**
     * Converts the CustomerTag object to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
