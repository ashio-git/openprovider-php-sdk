<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslStatisticsByStatus
{
    public function __construct(
        public readonly ?int $ACT = null,
        public readonly ?int $FAI = null,
        public readonly ?int $NSR = null,
        public readonly ?int $PEN = null,
        public readonly ?int $REJ = null,
        public readonly ?int $REQ = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ACT: $data['ACT'] ?? null,
            FAI: $data['FAI'] ?? null,
            NSR: $data['NSR'] ?? null,
            PEN: $data['PEN'] ?? null,
            REJ: $data['REJ'] ?? null,
            REQ: $data['REQ'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'ACT' => $this->ACT,
            'FAI' => $this->FAI,
            'NSR' => $this->NSR,
            'PEN' => $this->PEN,
            'REJ' => $this->REJ,
            'REQ' => $this->REQ,
        ], fn($v) => $v !== null);
    }
}