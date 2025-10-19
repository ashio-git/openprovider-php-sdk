<?php

declare(strict_types=1);

namespace Ashio\Model;

class DnsCreateZoneRequest
{
    public DomainName $domain;
    public ?string $isSpamexpertsEnabled = null;
    public ?string $masterIp = null;
    public ?string $provider = null;
    /** @var DnsRecord[]|null */
    public ?array $records = null;
    public ?bool $secured = null;
    public ?string $templateName = null;
    public ?string $type = null;

    public function __construct(DomainName $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return array<string,mixed>
     */
    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        $data = [
            'domain' => $this->domain->toArray(),
            'is_spamexperts_enabled' => $this->isSpamexpertsEnabled,
            'master_ip' => $this->masterIp,
            'provider' => $this->provider,
            'secured' => $this->secured,
            'template_name' => $this->templateName,
            'type' => $this->type,
        ];
        if ($this->records) {
            $data['records'] = array_map(static function (DnsRecord $record) {
                return array_filter([
                    'name' => $record->name,
                    'value' => $record->value,
                    'type' => $record->type,
                    'ttl' => $record->ttl,
                    'prio' => $record->prio,
                ], static fn ($v) => $v !== null);
            }, $this->records);
        }

        return array_filter($data, static fn ($v) => $v !== null);
    }
}
