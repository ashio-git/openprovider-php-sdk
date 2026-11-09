<?php

declare(strict_types=1);

namespace Ashio\Model;

class StatisticsSpamExpertsStatistics
{
    public ?string $activatedAt = null;
    public ?string $createdAt = null;
    public ?bool $exists = null;
    public ?string $expiredAt = null;
    public ?int $incomingCount = null;
    public ?int $incomingLimit = null;
    public ?int $outgoingCount = null;
    public ?int $outgoingLimit = null;
    public ?string $status = null;
    public ?bool $withOutgoingFilter = null;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->activatedAt = $data['activated_at'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
        $this->exists = $data['exists'] ?? null;
        $this->expiredAt = $data['expired_at'] ?? null;
        $this->incomingCount = $data['incoming_count'] ?? null;
        $this->incomingLimit = $data['incoming_limit'] ?? null;
        $this->outgoingCount = $data['outgoing_count'] ?? null;
        $this->outgoingLimit = $data['outgoing_limit'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->withOutgoingFilter = $data['with_outgoing_filter'] ?? null;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'activated_at' => $this->activatedAt,
            'created_at' => $this->createdAt,
            'exists' => $this->exists,
            'expired_at' => $this->expiredAt,
            'incoming_count' => $this->incomingCount,
            'incoming_limit' => $this->incomingLimit,
            'outgoing_count' => $this->outgoingCount,
            'outgoing_limit' => $this->outgoingLimit,
            'status' => $this->status,
            'with_outgoing_filter' => $this->withOutgoingFilter,
        ], static fn ($v) => $v !== null);
    }
}
