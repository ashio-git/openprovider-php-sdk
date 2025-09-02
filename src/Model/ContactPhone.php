<?php

declare(strict_types=1);

namespace Vexura\Model;

class ContactPhone
{
    /**
     * @var string|null The country code part of the phone number (including +).
     */
    public ?string $countryCode = null;

    /**
     * @var string|null The area code part of the phone number (excluding leading zero).
     */
    public ?string $areaCode = null;

    /**
     * @var string|null The subscriber number part of the phone number.
     */
    public ?string $subscriberNumber = null;

    /**
     * Populates the ContactPhone object from an array.
     *
     * @param array<string, mixed> $data The data to populate from.
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->countryCode = $data['country_code'] ?? null;
        $this->areaCode = $data['area_code'] ?? null;
        $this->subscriberNumber = $data['subscriber_number'] ?? null;
        return $this;
    }

    /**
     * Converts the ContactPhone object to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'country_code' => $this->countryCode,
            'area_code' => $this->areaCode,
            'subscriber_number' => $this->subscriberNumber,
        ];
    }
}
