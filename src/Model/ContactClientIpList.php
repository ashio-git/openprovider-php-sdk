<?php

declare(strict_types=1);

namespace Ashio\Model;

class ContactClientIpList
{
    /**
     * @var string[]|null A list of allowed IP addresses.
     */
    public ?array $allow = null;

    /**
     * @var string[]|null A list of denied IP addresses.
     */
    public ?array $deny = null;

    /**
     * Populates the ContactClientIpList object from an array.
     *
     * @param array<string, mixed> $data The data to populate from.
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->allow = $data['allow'] ?? null;
        $this->deny = $data['deny'] ?? null;
        return $this;
    }

    /**
     * Converts the ContactClientIpList object to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'allow' => $this->allow,
            'deny' => $this->deny,
        ];
    }
}
