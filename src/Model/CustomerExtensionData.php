<?php

declare(strict_types=1);

namespace Ashio\Model;

class CustomerExtensionData
{
    public ?string $adminType = null;
    public ?string $applicantPurpose = null;
    public ?string $birthDate = null;
    public ?string $companyNameCyrillic = null;
    public ?string $companyNameLatin = null;
    public ?string $companyRegistrationNumber = null;
    public ?string $contactTitle = null;
    public ?string $countryOfCitizenship = null;
    public ?string $customerUin = null;
    public ?string $ensAuthId = null;
    public ?string $ensKey = null;
    public ?string $firstNameCyrillic = null;
    public ?string $firstNameLatin = null;
    public ?string $hrMember = null;
    public ?string $industryClass = null;
    public ?string $isPrivateEnterpreneur = null;
    public ?string $lastNameCyrillic = null;
    public ?string $lastNameLatin = null;
    public ?string $legalAddressCyrillic = null;
    public ?string $legalType = null;
    public ?string $middleNameCyrillic = null;
    public ?string $middleNameLatin = null;
    public ?string $mobilePhoneNumber = null;
    public ?string $nexusCategory = null;
    public ?string $orgType = null;
    public ?string $passportIssueDate = null;
    public ?string $passportIssuer = null;
    public ?string $passportNumber = null;
    public ?string $passportSeries = null;
    public ?string $postalAddressCyrillic = null;
    public ?string $socialSecurityNumber = null;
    public ?string $taxPayerNumber = null;
    public ?string $uin = null;
    public ?string $userType = null;
    public ?string $vat = null;
    public ?string $website = null;
    public ?string $whoisEmail = null;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->adminType = $data['admin_type'] ?? null;
        $this->applicantPurpose = $data['applicant_purpose'] ?? null;
        $this->birthDate = $data['birth_date'] ?? null;
        $this->companyNameCyrillic = $data['company_name_cyrillic'] ?? null;
        $this->companyNameLatin = $data['company_name_latin'] ?? null;
        $this->companyRegistrationNumber = $data['company_registration_number'] ?? null;
        $this->contactTitle = $data['contact_title'] ?? null;
        $this->countryOfCitizenship = $data['country_of_citizenship'] ?? null;
        $this->customerUin = $data['customer_uin'] ?? null;
        $this->ensAuthId = $data['ens_auth_id'] ?? null;
        $this->ensKey = $data['ens_key'] ?? null;
        $this->firstNameCyrillic = $data['first_name_cyrillic'] ?? null;
        $this->firstNameLatin = $data['first_name_latin'] ?? null;
        $this->hrMember = $data['hr_member'] ?? null;
        $this->industryClass = $data['industry_class'] ?? null;
        $this->isPrivateEnterpreneur = $data['is_private_enterpreneur'] ?? null;
        $this->lastNameCyrillic = $data['last_name_cyrillic'] ?? null;
        $this->lastNameLatin = $data['last_name_latin'] ?? null;
        $this->legalAddressCyrillic = $data['legal_address_cyrillic'] ?? null;
        $this->legalType = $data['legal_type'] ?? null;
        $this->middleNameCyrillic = $data['middle_name_cyrillic'] ?? null;
        $this->middleNameLatin = $data['middle_name_latin'] ?? null;
        $this->mobilePhoneNumber = $data['mobile_phone_number'] ?? null;
        $this->nexusCategory = $data['nexus_category'] ?? null;
        $this->orgType = $data['org_type'] ?? null;
        $this->passportIssueDate = $data['passport_issue_date'] ?? null;
        $this->passportIssuer = $data['passport_issuer'] ?? null;
        $this->passportNumber = $data['passport_number'] ?? null;
        $this->passportSeries = $data['passport_series'] ?? null;
        $this->postalAddressCyrillic = $data['postal_address_cyrillic'] ?? null;
        $this->socialSecurityNumber = $data['social_security_number'] ?? null;
        $this->taxPayerNumber = $data['tax_payer_number'] ?? null;
        $this->uin = $data['uin'] ?? null;
        $this->userType = $data['user_type'] ?? null;
        $this->vat = $data['vat'] ?? null;
        $this->website = $data['website'] ?? null;
        $this->whoisEmail = $data['whois_email'] ?? null;

        return $this;
    }

    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        return array_filter([
            'admin_type' => $this->adminType,
            'applicant_purpose' => $this->applicantPurpose,
            'birth_date' => $this->birthDate,
            'company_name_cyrillic' => $this->companyNameCyrillic,
            'company_name_latin' => $this->companyNameLatin,
            'company_registration_number' => $this->companyRegistrationNumber,
            'contact_title' => $this->contactTitle,
            'country_of_citizenship' => $this->countryOfCitizenship,
            'customer_uin' => $this->customerUin,
            'ens_auth_id' => $this->ensAuthId,
            'ens_key' => $this->ensKey,
            'first_name_cyrillic' => $this->firstNameCyrillic,
            'first_name_latin' => $this->firstNameLatin,
            'hr_member' => $this->hrMember,
            'industry_class' => $this->industryClass,
            'is_private_enterpreneur' => $this->isPrivateEnterpreneur,
            'last_name_cyrillic' => $this->lastNameCyrillic,
            'last_name_latin' => $this->lastNameLatin,
            'legal_address_cyrillic' => $this->legalAddressCyrillic,
            'legal_type' => $this->legalType,
            'middle_name_cyrillic' => $this->middleNameCyrillic,
            'middle_name_latin' => $this->middleNameLatin,
            'mobile_phone_number' => $this->mobilePhoneNumber,
            'nexus_category' => $this->nexusCategory,
            'org_type' => $this->orgType,
            'passport_issue_date' => $this->passportIssueDate,
            'passport_issuer' => $this->passportIssuer,
            'passport_number' => $this->passportNumber,
            'passport_series' => $this->passportSeries,
            'postal_address_cyrillic' => $this->postalAddressCyrillic,
            'social_security_number' => $this->socialSecurityNumber,
            'tax_payer_number' => $this->taxPayerNumber,
            'uin' => $this->uin,
            'user_type' => $this->userType,
            'vat' => $this->vat,
            'website' => $this->website,
            'whois_email' => $this->whoisEmail,
        ], function ($value) {
            return $value !== null;
        });
    }
}
