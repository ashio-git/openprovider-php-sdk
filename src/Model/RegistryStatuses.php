<?php

declare(strict_types=1);

namespace Ashio\Model;

class RegistryStatuses
{
    public function __construct(
        public readonly ?RegistryStatus $client_hold = null,
        public readonly ?RegistryStatus $client_transfer_prohibited = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            client_hold: isset($data['client_hold']) ? RegistryStatus::fromArray($data['client_hold']) : null,
            client_transfer_prohibited: isset($data['client_transfer_prohibited']) ? RegistryStatus::fromArray($data['client_transfer_prohibited']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'client_hold' => $this->client_hold?->toArray(),
            'client_transfer_prohibited' => $this->client_transfer_prohibited?->toArray(),
        ], fn($v) => $v !== null);
    }
}