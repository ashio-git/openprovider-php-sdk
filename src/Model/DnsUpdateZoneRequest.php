<?php

declare(strict_types=1);

namespace Vexura\Model;

class DnsUpdateZoneRequest
{
    public ?DomainName $domain = null;
    public ?int $id = null;
    public ?bool $isSpamexpertsEnabled = null;
    public ?string $masterIp = null;
    public ?string $name = null;
    public ?DnsPremiumDnsData $premiumDns = null;
    public ?string $provider = null;
    public ?DnsRecordUpdates $records = null;
    public ?string $type = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'is_spamexperts_enabled' => $this->isSpamexpertsEnabled,
            'master_ip' => $this->masterIp,
            'name' => $this->name,
            'provider' => $this->provider,
            'type' => $this->type,
        ];
        if ($this->domain) {
            $data['domain'] = $this->domain->toArray();
        }
        if ($this->premiumDns) {
            $data['premium_dns'] = $this->premiumDns->toArray();
        }
        if ($this->records) {
            $data['records'] = $this->records->toArray();
        }
        return array_filter($data, static fn($v) => $v !== null);
    }
}
