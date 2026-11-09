<?php

declare(strict_types=1);

namespace Ashio\Model;

class RgpStatuses
{
    public function __construct(
        public readonly ?string $add_period = null,
        public readonly ?string $auto_renew_period = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            add_period: $data['add_period'] ?? null,
            auto_renew_period: $data['auto_renew_period'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'add_period' => $this->add_period,
            'auto_renew_period' => $this->auto_renew_period,
        ], fn ($v) => $v !== null);
    }
}
