<?php

declare(strict_types=1);

namespace Ashio\Model;

class RecordWithOriginal
{
    public function __construct(
        public readonly ?Record $original_record = null,
        public readonly ?Record $record = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            original_record: isset($data['original_record']) ? Record::fromArray($data['original_record']) : null,
            record: isset($data['record']) ? Record::fromArray($data['record']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'original_record' => $this->original_record?->toArray(),
            'record' => $this->record?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
