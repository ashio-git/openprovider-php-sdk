<?php

declare(strict_types=1);

namespace Ashio\Model;

class Specification
{
    public function __construct(
        public readonly ?string $description = null,
        public readonly ?string $name = null,
        /** @var SelectOption[]|null */
        public readonly ?array $options = null,
        public readonly ?bool $required = null,
        public readonly ?string $type = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            description: $data['description'] ?? null,
            name: $data['name'] ?? null,
            options: isset($data['options']) ? array_map(fn (array $item) => SelectOption::fromArray($item), $data['options']) : null,
            required: $data['required'] ?? null,
            type: $data['type'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'description' => $this->description,
            'name' => $this->name,
            'options' => $this->options !== null ? array_map(fn (SelectOption $item) => $item->toArray(), $this->options) : null,
            'required' => $this->required,
            'type' => $this->type,
        ], fn ($v) => $v !== null);
    }
}
