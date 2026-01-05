<?php

declare(strict_types=1);

namespace Ashio\Model;

class EasyDmarcOrder
{
    public function __construct(
        public readonly ?string $activated_at = null,
        public readonly ?Domain $domain = null,
        public readonly ?string $easydmarc_email_address = null,
        public readonly ?string $expired_at = null,
        public readonly ?int $id = null,
        public readonly ?RecentEasyDmarcAction $recent_easydmarc_action = null,
        public readonly ?string $record_host = null,
        public readonly ?string $record_type = null,
        public readonly ?string $record_value = null,
        public readonly ?string $renew_at = null,
        public readonly ?string $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            activated_at: $data['activated_at'] ?? null,
            domain: isset($data['domain']) ? Domain::fromArray($data['domain']) : null,
            easydmarc_email_address: $data['easydmarc_email_address'] ?? null,
            expired_at: $data['expired_at'] ?? null,
            id: $data['id'] ?? null,
            recent_easydmarc_action: isset($data['recent_easydmarc_action']) ? RecentEasyDmarcAction::fromArray($data['recent_easydmarc_action']) : null,
            record_host: $data['record_host'] ?? null,
            record_type: $data['record_type'] ?? null,
            record_value: $data['record_value'] ?? null,
            renew_at: $data['renew_at'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'activated_at' => $this->activated_at,
            'domain' => $this->domain?->toArray(),
            'easydmarc_email_address' => $this->easydmarc_email_address,
            'expired_at' => $this->expired_at,
            'id' => $this->id,
            'recent_easydmarc_action' => $this->recent_easydmarc_action?->toArray(),
            'record_host' => $this->record_host,
            'record_type' => $this->record_type,
            'record_value' => $this->record_value,
            'renew_at' => $this->renew_at,
            'status' => $this->status,
        ], fn($v) => $v !== null);
    }
}