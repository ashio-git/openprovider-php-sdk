<?php

declare(strict_types=1);

namespace Ashio\Model;

class Name
{
    public function __construct(
        public readonly ?string $first_name = null,
        public readonly ?string $full_name = null,
        public readonly ?string $initials = null,
        public readonly ?string $last_name = null,
        public readonly ?string $prefix = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            first_name: $data['first_name'] ?? null,
            full_name: $data['full_name'] ?? null,
            initials: $data['initials'] ?? null,
            last_name: $data['last_name'] ?? null,
            prefix: $data['prefix'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'first_name' => $this->first_name,
            'full_name' => $this->full_name,
            'initials' => $this->initials,
            'last_name' => $this->last_name,
            'prefix' => $this->prefix,
        ], fn ($v) => $v !== null);
    }
}
