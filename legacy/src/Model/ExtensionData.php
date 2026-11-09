<?php

declare(strict_types=1);

namespace Ashio\Model;

class ExtensionData
{
    public function __construct(
        public readonly ?string $admin_type = null,
        public readonly ?string $applicant_purpose = null,
        public readonly ?string $birth_date = null,
        public readonly ?string $company_name_cyrillic = null,
        public readonly ?string $company_name_latin = null,
        public readonly ?string $company_registration_number = null,
        public readonly ?string $contact_title = null,
        public readonly ?string $country_of_citizenship = null,
        public readonly ?string $customer_uin = null,
        public readonly ?string $ens_auth_id = null,
        public readonly ?string $ens_key = null,
        public readonly ?string $first_name_cyrillic = null,
        public readonly ?string $first_name_latin = null,
        public readonly ?string $hr_member = null,
        public readonly ?string $industry_class = null,
        public readonly ?string $is_private_enterpreneur = null,
        public readonly ?string $last_name_cyrillic = null,
        public readonly ?string $last_name_latin = null,
        public readonly ?string $legal_address_cyrillic = null,
        public readonly ?string $legal_type = null,
        public readonly ?string $middle_name_cyrillic = null,
        public readonly ?string $middle_name_latin = null,
        public readonly ?string $mobile_phone_number = null,
        public readonly ?string $nexus_category = null,
        public readonly ?string $org_type = null,
        public readonly ?string $passport_issue_date = null,
        public readonly ?string $passport_issuer = null,
        public readonly ?string $passport_number = null,
        public readonly ?string $passport_series = null,
        public readonly ?string $postal_address_cyrillic = null,
        public readonly ?string $social_security_number = null,
        public readonly ?string $tax_payer_number = null,
        public readonly ?string $uin = null,
        public readonly ?string $user_type = null,
        public readonly ?string $vat = null,
        public readonly ?string $website = null,
        public readonly ?string $whois_email = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            admin_type: $data['admin_type'] ?? null,
            applicant_purpose: $data['applicant_purpose'] ?? null,
            birth_date: $data['birth_date'] ?? null,
            company_name_cyrillic: $data['company_name_cyrillic'] ?? null,
            company_name_latin: $data['company_name_latin'] ?? null,
            company_registration_number: $data['company_registration_number'] ?? null,
            contact_title: $data['contact_title'] ?? null,
            country_of_citizenship: $data['country_of_citizenship'] ?? null,
            customer_uin: $data['customer_uin'] ?? null,
            ens_auth_id: $data['ens_auth_id'] ?? null,
            ens_key: $data['ens_key'] ?? null,
            first_name_cyrillic: $data['first_name_cyrillic'] ?? null,
            first_name_latin: $data['first_name_latin'] ?? null,
            hr_member: $data['hr_member'] ?? null,
            industry_class: $data['industry_class'] ?? null,
            is_private_enterpreneur: $data['is_private_enterpreneur'] ?? null,
            last_name_cyrillic: $data['last_name_cyrillic'] ?? null,
            last_name_latin: $data['last_name_latin'] ?? null,
            legal_address_cyrillic: $data['legal_address_cyrillic'] ?? null,
            legal_type: $data['legal_type'] ?? null,
            middle_name_cyrillic: $data['middle_name_cyrillic'] ?? null,
            middle_name_latin: $data['middle_name_latin'] ?? null,
            mobile_phone_number: $data['mobile_phone_number'] ?? null,
            nexus_category: $data['nexus_category'] ?? null,
            org_type: $data['org_type'] ?? null,
            passport_issue_date: $data['passport_issue_date'] ?? null,
            passport_issuer: $data['passport_issuer'] ?? null,
            passport_number: $data['passport_number'] ?? null,
            passport_series: $data['passport_series'] ?? null,
            postal_address_cyrillic: $data['postal_address_cyrillic'] ?? null,
            social_security_number: $data['social_security_number'] ?? null,
            tax_payer_number: $data['tax_payer_number'] ?? null,
            uin: $data['uin'] ?? null,
            user_type: $data['user_type'] ?? null,
            vat: $data['vat'] ?? null,
            website: $data['website'] ?? null,
            whois_email: $data['whois_email'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'admin_type' => $this->admin_type,
            'applicant_purpose' => $this->applicant_purpose,
            'birth_date' => $this->birth_date,
            'company_name_cyrillic' => $this->company_name_cyrillic,
            'company_name_latin' => $this->company_name_latin,
            'company_registration_number' => $this->company_registration_number,
            'contact_title' => $this->contact_title,
            'country_of_citizenship' => $this->country_of_citizenship,
            'customer_uin' => $this->customer_uin,
            'ens_auth_id' => $this->ens_auth_id,
            'ens_key' => $this->ens_key,
            'first_name_cyrillic' => $this->first_name_cyrillic,
            'first_name_latin' => $this->first_name_latin,
            'hr_member' => $this->hr_member,
            'industry_class' => $this->industry_class,
            'is_private_enterpreneur' => $this->is_private_enterpreneur,
            'last_name_cyrillic' => $this->last_name_cyrillic,
            'last_name_latin' => $this->last_name_latin,
            'legal_address_cyrillic' => $this->legal_address_cyrillic,
            'legal_type' => $this->legal_type,
            'middle_name_cyrillic' => $this->middle_name_cyrillic,
            'middle_name_latin' => $this->middle_name_latin,
            'mobile_phone_number' => $this->mobile_phone_number,
            'nexus_category' => $this->nexus_category,
            'org_type' => $this->org_type,
            'passport_issue_date' => $this->passport_issue_date,
            'passport_issuer' => $this->passport_issuer,
            'passport_number' => $this->passport_number,
            'passport_series' => $this->passport_series,
            'postal_address_cyrillic' => $this->postal_address_cyrillic,
            'social_security_number' => $this->social_security_number,
            'tax_payer_number' => $this->tax_payer_number,
            'uin' => $this->uin,
            'user_type' => $this->user_type,
            'vat' => $this->vat,
            'website' => $this->website,
            'whois_email' => $this->whois_email,
        ], fn ($v) => $v !== null);
    }
}
