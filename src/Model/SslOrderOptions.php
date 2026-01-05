<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslOrderOptions
{
    public function __construct(
        public readonly ?string $features = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            features: $data['features'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'features' => $this->features,
        ], fn($v) => $v !== null);
    }
}