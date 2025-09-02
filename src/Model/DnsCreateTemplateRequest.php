<?php

declare(strict_types=1);

namespace Ashio\Model;

class DnsCreateTemplateRequest
{
    public ?bool $isSpamexpertsEnabled = null;
    public string $name;
    /** @var DnsTemplateRecord[]|null */
    public ?array $records = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'is_spamexperts_enabled' => $this->isSpamexpertsEnabled,
        ];
        if ($this->records) {
            $data['records'] = array_map(static fn(DnsTemplateRecord $r) => $r->toArray(), $this->records);
        }
        return array_filter($data, static fn($v) => $v !== null);
    }
}
