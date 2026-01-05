<?php

declare(strict_types=1);

namespace Ashio\Model;

class CustomerAdditionalData
{
    public function __construct(
        /** @var File[]|null */
        public readonly ?array $attached_files = null,
        public readonly ?string $birth_address = null,
        public readonly ?string $birth_city = null,
        public readonly ?string $birth_country = null,
        public readonly ?string $birth_date = null,
        public readonly ?string $birth_state = null,
        public readonly ?string $birth_zipcode = null,
        public readonly ?string $company_registration_city = null,
        public readonly ?string $company_registration_number = null,
        public readonly ?string $company_registration_subscription_date = null,
        public readonly ?string $company_url = null,
        public readonly ?string $headquarters_address = null,
        public readonly ?string $headquarters_city = null,
        public readonly ?string $headquarters_country = null,
        public readonly ?string $headquarters_state = null,
        public readonly ?string $headquarters_zipcode = null,
        public readonly ?string $passport_number = null,
        public readonly ?string $social_security_number = null,
        public readonly ?string $trading_name = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            attached_files: isset($data['attached_files']) ? array_map(fn(array $item) => File::fromArray($item), $data['attached_files']) : null,
            birth_address: $data['birth_address'] ?? null,
            birth_city: $data['birth_city'] ?? null,
            birth_country: $data['birth_country'] ?? null,
            birth_date: $data['birth_date'] ?? null,
            birth_state: $data['birth_state'] ?? null,
            birth_zipcode: $data['birth_zipcode'] ?? null,
            company_registration_city: $data['company_registration_city'] ?? null,
            company_registration_number: $data['company_registration_number'] ?? null,
            company_registration_subscription_date: $data['company_registration_subscription_date'] ?? null,
            company_url: $data['company_url'] ?? null,
            headquarters_address: $data['headquarters_address'] ?? null,
            headquarters_city: $data['headquarters_city'] ?? null,
            headquarters_country: $data['headquarters_country'] ?? null,
            headquarters_state: $data['headquarters_state'] ?? null,
            headquarters_zipcode: $data['headquarters_zipcode'] ?? null,
            passport_number: $data['passport_number'] ?? null,
            social_security_number: $data['social_security_number'] ?? null,
            trading_name: $data['trading_name'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'attached_files' => $this->attached_files !== null ? array_map(fn(File $item) => $item->toArray(), $this->attached_files) : null,
            'birth_address' => $this->birth_address,
            'birth_city' => $this->birth_city,
            'birth_country' => $this->birth_country,
            'birth_date' => $this->birth_date,
            'birth_state' => $this->birth_state,
            'birth_zipcode' => $this->birth_zipcode,
            'company_registration_city' => $this->company_registration_city,
            'company_registration_number' => $this->company_registration_number,
            'company_registration_subscription_date' => $this->company_registration_subscription_date,
            'company_url' => $this->company_url,
            'headquarters_address' => $this->headquarters_address,
            'headquarters_city' => $this->headquarters_city,
            'headquarters_country' => $this->headquarters_country,
            'headquarters_state' => $this->headquarters_state,
            'headquarters_zipcode' => $this->headquarters_zipcode,
            'passport_number' => $this->passport_number,
            'social_security_number' => $this->social_security_number,
            'trading_name' => $this->trading_name,
        ], fn($v) => $v !== null);
    }
}