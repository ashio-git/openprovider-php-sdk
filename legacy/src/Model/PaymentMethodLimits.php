<?php

declare(strict_types=1);

namespace Ashio\Model;

class PaymentMethodLimits
{
    public function __construct(
        public readonly ?PaymentMethodLimit $maximum = null,
        public readonly ?PaymentMethodLimit $minimum = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            maximum: isset($data['maximum']) ? PaymentMethodLimit::fromArray($data['maximum']) : null,
            minimum: isset($data['minimum']) ? PaymentMethodLimit::fromArray($data['minimum']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'maximum' => $this->maximum?->toArray(),
            'minimum' => $this->minimum?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
