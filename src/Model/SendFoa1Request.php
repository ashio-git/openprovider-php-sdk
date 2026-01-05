<?php

declare(strict_types=1);

namespace Ashio\Model;

class SendFoa1Request
{
    public function __construct(
        public readonly ?Domain $domain = null,
        public readonly ?int $id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            domain: isset($data['domain']) ? Domain::fromArray($data['domain']) : null,
            id: $data['id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'domain' => $this->domain?->toArray(),
            'id' => $this->id,
        ], fn($v) => $v !== null);
    }
}