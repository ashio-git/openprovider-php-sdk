<?php

declare(strict_types=1);

namespace Ashio\Model;

class Phone
{
    public function __construct(
        public readonly ?string $area_code = null,
        public readonly ?string $country_code = null,
        public readonly ?string $subscriber_number = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            area_code: $data['area_code'] ?? null,
            country_code: $data['country_code'] ?? null,
            subscriber_number: $data['subscriber_number'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'area_code' => $this->area_code,
            'country_code' => $this->country_code,
            'subscriber_number' => $this->subscriber_number,
        ], fn($v) => $v !== null);
    }
}