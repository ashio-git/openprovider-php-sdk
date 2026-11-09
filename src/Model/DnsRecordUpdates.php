<?php

declare(strict_types=1);

namespace Ashio\Model;

class DnsRecordUpdates
{
    /** @var DnsRecord[]|null */
    public ?array $add = null;
    /** @var DnsRecord[]|null */
    public ?array $remove = null;
    /** @var DnsRecord[]|null */
    public ?array $replace = null;
    /** @var DnsRecordWithOriginal[]|null */
    public ?array $update = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->add) {
            $data['add'] = array_map(static fn (DnsRecord $r): array => self::recordToArray($r), $this->add);
        }
        if ($this->remove) {
            $data['remove'] = array_map(static fn (DnsRecord $r): array => self::recordToArray($r), $this->remove);
        }
        if ($this->replace) {
            $data['replace'] = array_map(static fn (DnsRecord $r): array => self::recordToArray($r), $this->replace);
        }
        if ($this->update) {
            $data['update'] = array_map(static fn (DnsRecordWithOriginal $r) => $r->toArray(), $this->update);
        }

        return $data;
    }

    /**
     * @param DnsRecord $record
     * @return array<string, mixed>
     */
    private static function recordToArray(DnsRecord $record): array
    {
        return array_filter([
            'name' => $record->name,
            'prio' => $record->prio,
            'ttl' => $record->ttl,
            'type' => $record->type,
            'value' => $record->value,
        ], static fn ($v) => $v !== null);
    }
}
