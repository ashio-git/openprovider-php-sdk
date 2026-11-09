<?php

declare(strict_types=1);

namespace Ashio\Model;

/**
 * Domain Token Request DTO
 * Used for creating domain tokens for DNS management
 */
class DomainTokenRequest
{
    /**
     * Domain name
     * @var DomainName|null
     */
    public ?DomainName $domain = null;

    /**
     * Token type
     * @var string|null
     */
    public ?string $type = null;

    /**
     * Token expiration date
     * @var string|null
     */
    public ?string $expirationDate = null;

    /**
     * Constructor
     * @param DomainName|null $domain
     * @param string|null $type
     * @param string|null $expirationDate
     */
    public function __construct(?DomainName $domain = null, ?string $type = null, ?string $expirationDate = null)
    {
        $this->domain = $domain;
        $this->type = $type;
        $this->expirationDate = $expirationDate;
    }

    /**
     * Convert to array for API request
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->domain !== null) {
            $data['domain'] = $this->domain->toArray();
        }

        if ($this->type !== null) {
            $data['type'] = $this->type;
        }

        if ($this->expirationDate !== null) {
            $data['expiration_date'] = $this->expirationDate;
        }

        return $data;
    }

    /**
     * Create from array
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        if (isset($data['domain'])) {
            $this->domain = (new DomainName())->fromArray($data['domain']);
        }

        $this->type = $data['type'] ?? null;
        $this->expirationDate = $data['expiration_date'] ?? null;

        return $this;
    }
}
