<?php

declare(strict_types=1);

namespace Vexura\Model;

class DnsRecordWithOriginal
{
    public ?DnsRecord $originalRecord = null;
    public ?DnsRecord $record = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'original_record' => $this->originalRecord ? [
                'name' => $this->originalRecord->name,
                'prio' => $this->originalRecord->prio,
                'ttl' => $this->originalRecord->ttl,
                'type' => $this->originalRecord->type,
                'value' => $this->originalRecord->value,
            ] : null,
            'record' => $this->record ? [
                'name' => $this->record->name,
                'prio' => $this->record->prio,
                'ttl' => $this->record->ttl,
                'type' => $this->record->type,
                'value' => $this->record->value,
            ] : null,
        ], static fn($v) => $v !== null);
    }
}
