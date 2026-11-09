<?php

declare(strict_types=1);

namespace Ashio\Model;

class CsrSubject
{
    public function __construct(
        public readonly ?string $common_name = null,
        public readonly ?string $country = null,
        public readonly ?string $email = null,
        public readonly ?string $locality = null,
        public readonly ?string $organization = null,
        public readonly ?string $state = null,
        public readonly ?string $unit = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            common_name: $data['common_name'] ?? null,
            country: $data['country'] ?? null,
            email: $data['email'] ?? null,
            locality: $data['locality'] ?? null,
            organization: $data['organization'] ?? null,
            state: $data['state'] ?? null,
            unit: $data['unit'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'common_name' => $this->common_name,
            'country' => $this->country,
            'email' => $this->email,
            'locality' => $this->locality,
            'organization' => $this->organization,
            'state' => $this->state,
            'unit' => $this->unit,
        ], fn ($v) => $v !== null);
    }
}
