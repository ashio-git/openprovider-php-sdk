<?php

declare(strict_types=1);

namespace Ashio\Model;

class AbuseDetails
{
    public function __construct(
        public readonly ?int $abuse_id = null,
        public readonly ?bool $is_domain_held = null,
        public readonly ?string $message = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            abuse_id: $data['abuse_id'] ?? null,
            is_domain_held: $data['is_domain_held'] ?? null,
            message: $data['message'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'abuse_id' => $this->abuse_id,
            'is_domain_held' => $this->is_domain_held,
            'message' => $this->message,
        ], fn($v) => $v !== null);
    }
}