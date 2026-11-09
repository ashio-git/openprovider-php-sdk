<?php

declare(strict_types=1);

namespace Ashio\Model;

class SpamExpertsStatistics
{
    public function __construct(
        public readonly ?string $activated_at = null,
        public readonly ?string $created_at = null,
        public readonly ?bool $exists = null,
        public readonly ?string $expired_at = null,
        public readonly ?int $incoming_count = null,
        public readonly ?int $incoming_limit = null,
        public readonly ?int $outgoing_count = null,
        public readonly ?int $outgoing_limit = null,
        public readonly ?string $status = null,
        public readonly ?bool $with_outgoing_filter = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            activated_at: $data['activated_at'] ?? null,
            created_at: $data['created_at'] ?? null,
            exists: $data['exists'] ?? null,
            expired_at: $data['expired_at'] ?? null,
            incoming_count: $data['incoming_count'] ?? null,
            incoming_limit: $data['incoming_limit'] ?? null,
            outgoing_count: $data['outgoing_count'] ?? null,
            outgoing_limit: $data['outgoing_limit'] ?? null,
            status: $data['status'] ?? null,
            with_outgoing_filter: $data['with_outgoing_filter'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'activated_at' => $this->activated_at,
            'created_at' => $this->created_at,
            'exists' => $this->exists,
            'expired_at' => $this->expired_at,
            'incoming_count' => $this->incoming_count,
            'incoming_limit' => $this->incoming_limit,
            'outgoing_count' => $this->outgoing_count,
            'outgoing_limit' => $this->outgoing_limit,
            'status' => $this->status,
            'with_outgoing_filter' => $this->with_outgoing_filter,
        ], fn ($v) => $v !== null);
    }
}
