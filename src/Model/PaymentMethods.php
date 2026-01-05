<?php

declare(strict_types=1);

namespace Ashio\Model;

class PaymentMethods
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?PaymentMethodSettings $settings = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            settings: isset($data['settings']) ? PaymentMethodSettings::fromArray($data['settings']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'settings' => $this->settings?->toArray(),
        ], fn($v) => $v !== null);
    }
}