<?php

declare(strict_types=1);

namespace Ashio\Model;

class PaymentMethodLimits
{
    public function __construct(
        public readonly ?PaymentMethodLimit $maximum = null,
        public readonly ?PaymentMethodLimit $minimum = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            maximum: isset($data['maximum']) ? PaymentMethodLimit::fromArray($data['maximum']) : null,
            minimum: isset($data['minimum']) ? PaymentMethodLimit::fromArray($data['minimum']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'maximum' => $this->maximum?->toArray(),
            'minimum' => $this->minimum?->toArray(),
        ], fn($v) => $v !== null);
    }
}