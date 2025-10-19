<?php

declare(strict_types=1);

namespace Ashio\Model;

/**
 * Domain Token Response DTO
 * Represents a domain token for DNS management
 */
class DomainToken
{
    /**
     * Token ID
     * @var int|null
     */
    public ?int $id = null;

    /**
     * Token string
     * @var string|null
     */
    public ?string $token = null;

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
     * Creation date
     * @var string|null
     */
    public ?string $creationDate = null;

    /**
     * Expiration date
     * @var string|null
     */
    public ?string $expirationDate = null;

    /**
     * Is active
     * @var bool|null
     */
    public ?bool $isActive = null;

    /**
     * Constructor
     * @param array<string, mixed> $data
     */
    /**

     * @param array<string, mixed> $data

     */
    public function __construct(array $data = [])
    {
        if ($data) {
            $this->fromArray($data);
        }
    }

    /**
     * Create from array
     * @param array<string, mixed> $data
     * @return self
     */
    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->token = $data['token'] ?? null;

        if (isset($data['domain'])) {
            $this->domain = (new DomainName())->fromArray($data['domain']);
        }

        $this->type = $data['type'] ?? null;
        $this->creationDate = $data['creation_date'] ?? null;
        $this->expirationDate = $data['expiration_date'] ?? null;
        $this->isActive = $data['is_active'] ?? null;

        return $this;
    }

    /**
     * Convert to array
     * @return array<string, mixed>
     */
    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'token' => $this->token,
            'type' => $this->type,
            'creation_date' => $this->creationDate,
            'expiration_date' => $this->expirationDate,
            'is_active' => $this->isActive,
        ];

        if ($this->domain !== null) {
            $data['domain'] = $this->domain->toArray();
        }

        return array_filter($data, static fn ($v) => $v !== null);
    }
}
