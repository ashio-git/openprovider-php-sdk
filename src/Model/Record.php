<?php

declare(strict_types=1);

namespace Ashio\Model;

class Record
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?int $prio = null,
        public readonly ?int $ttl = null,
        public readonly ?string $type = null,
        public readonly ?string $value = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            prio: $data['prio'] ?? null,
            ttl: $data['ttl'] ?? null,
            type: $data['type'] ?? null,
            value: $data['value'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'prio' => $this->prio,
            'ttl' => $this->ttl,
            'type' => $this->type,
            'value' => $this->value,
        ], fn($v) => $v !== null);
    }
}