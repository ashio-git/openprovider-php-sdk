<?php

declare(strict_types=1);

namespace Ashio\Model;

class ContactAddress
{
    /**
     * @var string|null The street name.
     */
    public ?string $street = null;

    /**
     * @var string|null The house number.
     */
    public ?string $number = null;

    /**
     * @var string|null The house number suffix.
     */
    public ?string $suffix = null;

    /**
     * @var string|null The postal code.
     */
    public ?string $zipcode = null;

    /**
     * @var string|null The city.
     */
    public ?string $city = null;

    /**
     * @var string|null The state or province.
     */
    public ?string $state = null;

    /**
     * @var string|null The country code (e.g., US, NL).
     */
    public ?string $country = null;

    /**
     * Populates the ContactAddress object from an array.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->street = $data['street'] ?? null;
        $this->number = $data['number'] ?? null;
        $this->suffix = $data['suffix'] ?? null;
        $this->zipcode = $data['zipcode'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->state = $data['state'] ?? null;
        $this->country = $data['country'] ?? null;

        return $this;
    }

    /**
     * Converts the ContactAddress object to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'number' => $this->number,
            'suffix' => $this->suffix,
            'zipcode' => $this->zipcode,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
        ];
    }
}
