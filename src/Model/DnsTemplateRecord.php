<?php

declare(strict_types=1);

namespace Ashio\Model;

class DnsTemplateRecord
{
    public ?int $id = null;
    public ?string $name = null;
    public ?int $prio = null;
    public ?int $ttl = null;
    public ?string $type = null;
    public ?string $value = null;

    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->prio = $data['prio'] ?? null;
        $this->ttl = $data['ttl'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->value = $data['value'] ?? null;
        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'prio' => $this->prio,
            'ttl' => $this->ttl,
            'type' => $this->type,
            'value' => $this->value,
        ], function ($value) {
            return $value !== null;
        });
    }
}
