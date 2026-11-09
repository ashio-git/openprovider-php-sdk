<?php

declare(strict_types=1);

namespace Ashio\Model;

class ContactName
{
    /**
     * @var string|null The contact's initials.
     */
    public ?string $initials = null;

    /**
     * @var string|null The contact's first name.
     */
    public ?string $firstName = null;

    /**
     * @var string|null The contact's name prefix (e.g., 'van de').
     */
    public ?string $prefix = null;

    /**
     * @var string|null The contact's last name.
     */
    public ?string $lastName = null;

    /**
     * @var string|null The contact's full name.
     */
    public ?string $fullName = null;

    /**
     * Populates the ContactName object from an array.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->initials = $data['initials'] ?? null;
        $this->firstName = $data['first_name'] ?? null;
        $this->prefix = $data['prefix'] ?? null;
        $this->lastName = $data['last_name'] ?? null;
        $this->fullName = $data['full_name'] ?? null;

        return $this;
    }

    /**
     * Converts the ContactName object to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'initials' => $this->initials,
            'first_name' => $this->firstName,
            'prefix' => $this->prefix,
            'last_name' => $this->lastName,
            'full_name' => $this->fullName,
        ];
    }
}
