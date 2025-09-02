<?php

declare(strict_types=1);

namespace Vexura\Model;

class ContactAdditionalData
{
    /**
     * @var string|null The contact's birth date.
     */
    public ?string $birthDate = null;

    /**
     * @var string|null The contact's birth city.
     */
    public ?string $birthCity = null;

    /**
     * @var string|null The contact's social security number.
     */
    public ?string $socialSecurityNumber = null;

    /**
     * Populates the ContactAdditionalData object from an array.
     *
     * @param array<string, mixed> $data The data to populate from.
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->birthDate = $data['birth_date'] ?? null;
        $this->birthCity = $data['birth_city'] ?? null;
        $this->socialSecurityNumber = $data['social_security_number'] ?? null;
        return $this;
    }

    /**
     * Converts the ContactAdditionalData object to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'birth_date' => $this->birthDate,
            'birth_city' => $this->birthCity,
            'social_security_number' => $this->socialSecurityNumber,
        ];
    }
}
