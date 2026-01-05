<?php

declare(strict_types=1);

namespace Ashio\Model;

class History
{
    public function __construct(
        public readonly ?string $contents = null,
        public readonly ?string $domain = null,
        public readonly ?string $ip = null,
        public readonly ?string $subject = null,
        public readonly ?string $timestamp = null,
        public readonly ?string $type = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contents: $data['contents'] ?? null,
            domain: $data['domain'] ?? null,
            ip: $data['ip'] ?? null,
            subject: $data['subject'] ?? null,
            timestamp: $data['timestamp'] ?? null,
            type: $data['type'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'contents' => $this->contents,
            'domain' => $this->domain,
            'ip' => $this->ip,
            'subject' => $this->subject,
            'timestamp' => $this->timestamp,
            'type' => $this->type,
        ], fn($v) => $v !== null);
    }
}