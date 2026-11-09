<?php

declare(strict_types=1);

namespace Ashio\Model;

/**
 * Registry-specific data accepted when updating a domain (spec: domainUpdateAdditionalData).
 */
class UpdateAdditionalData
{
    public function __construct(
        public readonly ?string $abogado_acceptance = null,
        public readonly ?string $admin_sing_pass_id = null,
        public readonly ?string $auth_code = null,
        public readonly ?string $company_registration_number = null,
        public readonly ?string $consent_for_publishing = null,
        public readonly ?string $customer_uin = null,
        public readonly ?UINDocumentType $customer_uin_doc_type = null,
        /** @var list<string>|null */
        public readonly ?array $domain_name_variants = null,
        public readonly ?string $intended_use = null,
        public readonly ?string $it_nexus_declaration = null,
        public readonly ?string $it_reseller_declaration = null,
        public readonly ?string $law_acceptance = null,
        public readonly ?string $legal_type = null,
        public readonly ?string $maintainer = null,
        public readonly ?string $membership_id = null,
        public readonly ?string $mobile_phone_number_verification = null,
        public readonly ?string $ngo_ong_eligibility_acceptance = null,
        public readonly ?string $ngo_ong_policy_acceptance = null,
        public readonly ?string $nz_privacy_option = null,
        public readonly ?string $passport_number = null,
        public readonly ?string $vat = null,
        public readonly ?string $verification_code = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            abogado_acceptance: $data['abogado_acceptance'] ?? null,
            admin_sing_pass_id: $data['admin_sing_pass_id'] ?? null,
            auth_code: $data['auth_code'] ?? null,
            company_registration_number: $data['company_registration_number'] ?? null,
            consent_for_publishing: $data['consent_for_publishing'] ?? null,
            customer_uin: $data['customer_uin'] ?? null,
            customer_uin_doc_type: isset($data['customer_uin_doc_type']) ? UINDocumentType::fromArray($data['customer_uin_doc_type']) : null,
            domain_name_variants: $data['domain_name_variants'] ?? null,
            intended_use: $data['intended_use'] ?? null,
            it_nexus_declaration: $data['it_nexus_declaration'] ?? null,
            it_reseller_declaration: $data['it_reseller_declaration'] ?? null,
            law_acceptance: $data['law_acceptance'] ?? null,
            legal_type: $data['legal_type'] ?? null,
            maintainer: $data['maintainer'] ?? null,
            membership_id: $data['membership_id'] ?? null,
            mobile_phone_number_verification: $data['mobile_phone_number_verification'] ?? null,
            ngo_ong_eligibility_acceptance: $data['ngo_ong_eligibility_acceptance'] ?? null,
            ngo_ong_policy_acceptance: $data['ngo_ong_policy_acceptance'] ?? null,
            nz_privacy_option: $data['nz_privacy_option'] ?? null,
            passport_number: $data['passport_number'] ?? null,
            vat: $data['vat'] ?? null,
            verification_code: $data['verification_code'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'abogado_acceptance' => $this->abogado_acceptance,
            'admin_sing_pass_id' => $this->admin_sing_pass_id,
            'auth_code' => $this->auth_code,
            'company_registration_number' => $this->company_registration_number,
            'consent_for_publishing' => $this->consent_for_publishing,
            'customer_uin' => $this->customer_uin,
            'customer_uin_doc_type' => $this->customer_uin_doc_type?->toArray(),
            'domain_name_variants' => $this->domain_name_variants,
            'intended_use' => $this->intended_use,
            'it_nexus_declaration' => $this->it_nexus_declaration,
            'it_reseller_declaration' => $this->it_reseller_declaration,
            'law_acceptance' => $this->law_acceptance,
            'legal_type' => $this->legal_type,
            'maintainer' => $this->maintainer,
            'membership_id' => $this->membership_id,
            'mobile_phone_number_verification' => $this->mobile_phone_number_verification,
            'ngo_ong_eligibility_acceptance' => $this->ngo_ong_eligibility_acceptance,
            'ngo_ong_policy_acceptance' => $this->ngo_ong_policy_acceptance,
            'nz_privacy_option' => $this->nz_privacy_option,
            'passport_number' => $this->passport_number,
            'vat' => $this->vat,
            'verification_code' => $this->verification_code,
        ], fn ($v) => $v !== null);
    }
}
