<?php

declare(strict_types=1);

namespace Ashio\Model;

class RecordUpdates
{
    public function __construct(
        /** @var Record[]|null */
        public readonly ?array $add = null,
        /** @var Record[]|null */
        public readonly ?array $remove = null,
        /** @var Record[]|null */
        public readonly ?array $replace = null,
        /** @var RecordWithOriginal[]|null */
        public readonly ?array $update = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            add: isset($data['add']) ? array_map(fn (array $item) => Record::fromArray($item), $data['add']) : null,
            remove: isset($data['remove']) ? array_map(fn (array $item) => Record::fromArray($item), $data['remove']) : null,
            replace: isset($data['replace']) ? array_map(fn (array $item) => Record::fromArray($item), $data['replace']) : null,
            update: isset($data['update']) ? array_map(fn (array $item) => RecordWithOriginal::fromArray($item), $data['update']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'add' => $this->add !== null ? array_map(fn (Record $item) => $item->toArray(), $this->add) : null,
            'remove' => $this->remove !== null ? array_map(fn (Record $item) => $item->toArray(), $this->remove) : null,
            'replace' => $this->replace !== null ? array_map(fn (Record $item) => $item->toArray(), $this->replace) : null,
            'update' => $this->update !== null ? array_map(fn (RecordWithOriginal $item) => $item->toArray(), $this->update) : null,
        ], fn ($v) => $v !== null);
    }
}
