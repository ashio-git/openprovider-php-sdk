<?php

declare(strict_types=1);

namespace Ashio\Model;

class Address
{
    public function __construct(
        public readonly ?string $city = null,
        public readonly ?string $country = null,
        public readonly ?string $number = null,
        public readonly ?string $state = null,
        public readonly ?string $street = null,
        public readonly ?string $suffix = null,
        public readonly ?string $zipcode = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            city: $data['city'] ?? null,
            country: $data['country'] ?? null,
            number: $data['number'] ?? null,
            state: $data['state'] ?? null,
            street: $data['street'] ?? null,
            suffix: $data['suffix'] ?? null,
            zipcode: $data['zipcode'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'city' => $this->city,
            'country' => $this->country,
            'number' => $this->number,
            'state' => $this->state,
            'street' => $this->street,
            'suffix' => $this->suffix,
            'zipcode' => $this->zipcode,
        ], fn ($v) => $v !== null);
    }
}
