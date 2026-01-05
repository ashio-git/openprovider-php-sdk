<?php

declare(strict_types=1);

namespace Ashio\Model;

class CreateTemplateRequest
{
    public function __construct(
        public readonly ?bool $is_spamexperts_enabled = null,
        public readonly ?string $name = null,
        /** @var Record[]|null */
        public readonly ?array $records = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            is_spamexperts_enabled: $data['is_spamexperts_enabled'] ?? null,
            name: $data['name'] ?? null,
            records: isset($data['records']) ? array_map(fn(array $item) => Record::fromArray($item), $data['records']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'is_spamexperts_enabled' => $this->is_spamexperts_enabled,
            'name' => $this->name,
            'records' => $this->records !== null ? array_map(fn(Record $item) => $item->toArray(), $this->records) : null,
        ], fn($v) => $v !== null);
    }
}