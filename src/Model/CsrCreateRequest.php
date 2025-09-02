<?php

declare(strict_types=1);

namespace Vexura\Model;

class CsrCreateRequest
{
    public ?int $bits;
    public string $common_name;
    public ?string $country;
    public ?string $email;
    public ?string $locality;
    public ?string $organization;
    public ?string $signature_hash_algorithm;
    public ?string $state;
    /** @var string[]|null */
    public ?array $subject_alternative_name;
    public ?string $unit;
    public ?bool $with_config;

    /**
     * @param string[]|null $subjectAlternativeName
     */
    public function __construct(
        string $commonName,
        ?int $bits = null,
        ?string $country = null,
        ?string $email = null,
        ?string $locality = null,
        ?string $organization = null,
        ?string $signatureHashAlgorithm = null,
        ?string $state = null,
        ?array $subjectAlternativeName = null,
        ?string $unit = null,
        ?bool $withConfig = null
    ) {
        $this->common_name = $commonName;
        $this->bits = $bits;
        $this->country = $country;
        $this->email = $email;
        $this->locality = $locality;
        $this->organization = $organization;
        $this->signature_hash_algorithm = $signatureHashAlgorithm;
        $this->state = $state;
        $this->subject_alternative_name = $subjectAlternativeName;
        $this->unit = $unit;
        $this->with_config = $withConfig;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'bits' => $this->bits,
            'common_name' => $this->common_name,
            'country' => $this->country,
            'email' => $this->email,
            'locality' => $this->locality,
            'organization' => $this->organization,
            'signature_hash_algorithm' => $this->signature_hash_algorithm,
            'state' => $this->state,
            'subject_alternative_name' => $this->subject_alternative_name,
            'unit' => $this->unit,
            'with_config' => $this->with_config,
        ], static fn($v) => $v !== null);
    }
}
