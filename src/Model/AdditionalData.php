<?php

declare(strict_types=1);

namespace Ashio\Model;

class AdditionalData
{
    public function __construct(
        public readonly ?string $abogado_acceptance = null,
        public readonly ?string $admin_sing_pass_id = null,
        public readonly ?string $ae_acceptance = null,
        public readonly ?string $allocation_token = null,
        public readonly ?string $auth_code = null,
        public readonly ?string $bank_acceptance = null,
        public readonly ?string $company_registration_number = null,
        public readonly ?string $coop_acceptance = null,
        public readonly ?string $customer_uin = null,
        public readonly ?UINDocumentType $customer_uin_doc_type = null,
        /** @var string[]|null */
        public readonly ?array $domain_name_variants = null,
        public readonly ?string $eligibility_type = null,
        public readonly ?string $eligibility_type_relationship = null,
        public readonly ?string $es_annex_acceptance = null,
        public readonly ?string $ftld_token = null,
        public readonly ?string $gay_donation_acceptance = null,
        public readonly ?string $gay_rights_protection_acceptance = null,
        public readonly ?string $id_number = null,
        public readonly ?string $id_type = null,
        public readonly ?string $idn_script = null,
        public readonly ?string $insurance_acceptance = null,
        public readonly ?string $intended_use = null,
        public readonly ?string $law_acceptance = null,
        public readonly ?string $legal_type = null,
        public readonly ?string $maintainer = null,
        public readonly ?string $membership_id = null,
        public readonly ?string $mobile_phone_number_verification = null,
        public readonly ?string $ngo_ong_eligibility_acceptance = null,
        public readonly ?string $ngo_ong_policy_acceptance = null,
        public readonly ?string $passport_number = null,
        public readonly ?string $rurf_blocked_domains = null,
        public readonly ?string $self_service = null,
        public readonly ?string $trademark = null,
        public readonly ?string $trademark_id = null,
        public readonly ?string $travel_acceptance = null,
        public readonly ?string $vat = null,
        public readonly ?string $verification_code = null,
        public readonly ?string $vote_acceptance = null,
        public readonly ?string $voto_acceptance = null,
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
            ae_acceptance: $data['ae_acceptance'] ?? null,
            allocation_token: $data['allocation_token'] ?? null,
            auth_code: $data['auth_code'] ?? null,
            bank_acceptance: $data['bank_acceptance'] ?? null,
            company_registration_number: $data['company_registration_number'] ?? null,
            coop_acceptance: $data['coop_acceptance'] ?? null,
            customer_uin: $data['customer_uin'] ?? null,
            customer_uin_doc_type: isset($data['customer_uin_doc_type']) ? UINDocumentType::fromArray($data['customer_uin_doc_type']) : null,
            domain_name_variants: $data['domain_name_variants'] ?? null,
            eligibility_type: $data['eligibility_type'] ?? null,
            eligibility_type_relationship: $data['eligibility_type_relationship'] ?? null,
            es_annex_acceptance: $data['es_annex_acceptance'] ?? null,
            ftld_token: $data['ftld_token'] ?? null,
            gay_donation_acceptance: $data['gay_donation_acceptance'] ?? null,
            gay_rights_protection_acceptance: $data['gay_rights_protection_acceptance'] ?? null,
            id_number: $data['id_number'] ?? null,
            id_type: $data['id_type'] ?? null,
            idn_script: $data['idn_script'] ?? null,
            insurance_acceptance: $data['insurance_acceptance'] ?? null,
            intended_use: $data['intended_use'] ?? null,
            law_acceptance: $data['law_acceptance'] ?? null,
            legal_type: $data['legal_type'] ?? null,
            maintainer: $data['maintainer'] ?? null,
            membership_id: $data['membership_id'] ?? null,
            mobile_phone_number_verification: $data['mobile_phone_number_verification'] ?? null,
            ngo_ong_eligibility_acceptance: $data['ngo_ong_eligibility_acceptance'] ?? null,
            ngo_ong_policy_acceptance: $data['ngo_ong_policy_acceptance'] ?? null,
            passport_number: $data['passport_number'] ?? null,
            rurf_blocked_domains: $data['rurf_blocked_domains'] ?? null,
            self_service: $data['self_service'] ?? null,
            trademark: $data['trademark'] ?? null,
            trademark_id: $data['trademark_id'] ?? null,
            travel_acceptance: $data['travel_acceptance'] ?? null,
            vat: $data['vat'] ?? null,
            verification_code: $data['verification_code'] ?? null,
            vote_acceptance: $data['vote_acceptance'] ?? null,
            voto_acceptance: $data['voto_acceptance'] ?? null,
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
            'ae_acceptance' => $this->ae_acceptance,
            'allocation_token' => $this->allocation_token,
            'auth_code' => $this->auth_code,
            'bank_acceptance' => $this->bank_acceptance,
            'company_registration_number' => $this->company_registration_number,
            'coop_acceptance' => $this->coop_acceptance,
            'customer_uin' => $this->customer_uin,
            'customer_uin_doc_type' => $this->customer_uin_doc_type?->toArray(),
            'domain_name_variants' => $this->domain_name_variants,
            'eligibility_type' => $this->eligibility_type,
            'eligibility_type_relationship' => $this->eligibility_type_relationship,
            'es_annex_acceptance' => $this->es_annex_acceptance,
            'ftld_token' => $this->ftld_token,
            'gay_donation_acceptance' => $this->gay_donation_acceptance,
            'gay_rights_protection_acceptance' => $this->gay_rights_protection_acceptance,
            'id_number' => $this->id_number,
            'id_type' => $this->id_type,
            'idn_script' => $this->idn_script,
            'insurance_acceptance' => $this->insurance_acceptance,
            'intended_use' => $this->intended_use,
            'law_acceptance' => $this->law_acceptance,
            'legal_type' => $this->legal_type,
            'maintainer' => $this->maintainer,
            'membership_id' => $this->membership_id,
            'mobile_phone_number_verification' => $this->mobile_phone_number_verification,
            'ngo_ong_eligibility_acceptance' => $this->ngo_ong_eligibility_acceptance,
            'ngo_ong_policy_acceptance' => $this->ngo_ong_policy_acceptance,
            'passport_number' => $this->passport_number,
            'rurf_blocked_domains' => $this->rurf_blocked_domains,
            'self_service' => $this->self_service,
            'trademark' => $this->trademark,
            'trademark_id' => $this->trademark_id,
            'travel_acceptance' => $this->travel_acceptance,
            'vat' => $this->vat,
            'verification_code' => $this->verification_code,
            'vote_acceptance' => $this->vote_acceptance,
            'voto_acceptance' => $this->voto_acceptance,
        ], fn ($v) => $v !== null);
    }
}
