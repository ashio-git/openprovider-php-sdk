<?php

declare(strict_types=1);

namespace Ashio\Model;

class CustomerAdditionalData
{
    /** @var CustomerFile[]|null */
    public ?array $attachedFiles = null;
    public ?string $birthAddress = null;
    public ?string $birthCity = null;
    public ?string $birthCountry = null;
    public ?string $birthDate = null;
    public ?string $birthState = null;
    public ?string $birthZipcode = null;
    public ?string $companyRegistrationCity = null;
    public ?string $companyRegistrationNumber = null;
    public ?string $companyRegistrationSubscriptionDate = null;
    public ?string $companyUrl = null;
    public ?string $headquartersAddress = null;
    public ?string $headquartersCity = null;
    public ?string $headquartersCountry = null;
    public ?string $headquartersState = null;
    public ?string $headquartersZipcode = null;
    public ?string $passportNumber = null;
    public ?string $socialSecurityNumber = null;
    public ?string $tradingName = null;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        if (isset($data['attached_files'])) {
            $this->attachedFiles = array_map(function ($fileData) {
                return (new CustomerFile())->fromArray($fileData);
            }, $data['attached_files']);
        }
        $this->birthAddress = $data['birth_address'] ?? null;
        $this->birthCity = $data['birth_city'] ?? null;
        $this->birthCountry = $data['birth_country'] ?? null;
        $this->birthDate = $data['birth_date'] ?? null;
        $this->birthState = $data['birth_state'] ?? null;
        $this->birthZipcode = $data['birth_zipcode'] ?? null;
        $this->companyRegistrationCity = $data['company_registration_city'] ?? null;
        $this->companyRegistrationNumber = $data['company_registration_number'] ?? null;
        $this->companyRegistrationSubscriptionDate = $data['company_registration_subscription_date'] ?? null;
        $this->companyUrl = $data['company_url'] ?? null;
        $this->headquartersAddress = $data['headquarters_address'] ?? null;
        $this->headquartersCity = $data['headquarters_city'] ?? null;
        $this->headquartersCountry = $data['headquarters_country'] ?? null;
        $this->headquartersState = $data['headquarters_state'] ?? null;
        $this->headquartersZipcode = $data['headquarters_zipcode'] ?? null;
        $this->passportNumber = $data['passport_number'] ?? null;
        $this->socialSecurityNumber = $data['social_security_number'] ?? null;
        $this->tradingName = $data['trading_name'] ?? null;

        return $this;
    }

    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        $data = [
            'birth_address' => $this->birthAddress,
            'birth_city' => $this->birthCity,
            'birth_country' => $this->birthCountry,
            'birth_date' => $this->birthDate,
            'birth_state' => $this->birthState,
            'birth_zipcode' => $this->birthZipcode,
            'company_registration_city' => $this->companyRegistrationCity,
            'company_registration_number' => $this->companyRegistrationNumber,
            'company_registration_subscription_date' => $this->companyRegistrationSubscriptionDate,
            'company_url' => $this->companyUrl,
            'headquarters_address' => $this->headquartersAddress,
            'headquarters_city' => $this->headquartersCity,
            'headquarters_country' => $this->headquartersCountry,
            'headquarters_state' => $this->headquartersState,
            'headquarters_zipcode' => $this->headquartersZipcode,
            'passport_number' => $this->passportNumber,
            'social_security_number' => $this->socialSecurityNumber,
            'trading_name' => $this->tradingName,
        ];

        if ($this->attachedFiles) {
            $data['attached_files'] = array_map(function (CustomerFile $file) {
                return $file->toArray();
            }, $this->attachedFiles);
        }

        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }
}
