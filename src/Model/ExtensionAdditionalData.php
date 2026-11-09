<?php

declare(strict_types=1);

namespace Ashio\Model;

class ExtensionAdditionalData
{
    public function __construct(
        public readonly ?ExtensionData $data = null,
        public readonly ?string $name = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            data: isset($data['data']) ? ExtensionData::fromArray($data['data']) : null,
            name: $data['name'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'data' => $this->data?->toArray(),
            'name' => $this->name,
        ], fn ($v) => $v !== null);
    }
}
