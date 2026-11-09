<?php

declare(strict_types=1);

namespace Ashio\Model;

class PremiumDnsData
{
    public function __construct(
        public readonly ?SectigoData $sectigo = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sectigo: isset($data['sectigo']) ? SectigoData::fromArray($data['sectigo']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'sectigo' => $this->sectigo?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
