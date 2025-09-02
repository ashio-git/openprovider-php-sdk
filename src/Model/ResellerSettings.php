<?php

declare(strict_types=1);

namespace Vexura\Model;

class ResellerSettings
{
    public ?string $avHandle = null;
    public ?string $claimsConfirmUrl = null;
    public ?string $claimsEmailSubject = null;
    public ?string $claimsResellerName = null;
    public ?string $claimsSenderEmail = null;
    public ?string $currency = null;
    public ?string $defaultDomainBillingHandle = null;
    public ?string $defaultDomainResellerHandle = null;
    public ?string $foaConfirmUrl = null;
    public ?string $foaEmailSubject = null;
    public ?string $foaResellerName = null;
    public ?string $foaSenderEmail = null;
    public ?string $foaTermsConditionsUrl = null;
    public ?bool $isAutoRenewEnabled = null;
    public ?bool $isCustomNsesEnabled = null;
    public ?string $ive2CustomizedBody = null;
    public ?string $ive2EmailSubject = null;
    public ?string $iveConfirmUrl = null;
    public ?string $iveCustomizedBody = null;
    public ?string $iveEmailSubject = null;
    public ?string $iveResellerName = null;
    public ?string $iveSenderEmail = null;
    public ?string $jurisdiction = null;
    public ?string $language = null;
    public ?float $maximumCredit = null;
    public ?float $maximumCreditCardPayment = null;
    public ?float $maximumIDealPayment = null;
    public ?float $maximumWesternUnionPayment = null;
    public ?float $minimumPayment = null;
    public ?float $minimumWesternUnionPayment = null;
    /** @var string[]|null */
    public ?array $payMethods = null;
    /** @var SettingsPaymentMethods[]|null */
    public ?array $paymentMethods = null;
    public ?string $preregisteredDomainPriorities = null;
    /** @var SettingsPaymentMethods[]|null */
    public ?array $recurringPaymentMethods = null;
    /** @var int[]|null */
    public ?array $renewNotificationDays = null;
    public ?bool $renewNotificationEveryWeek = null;
    public ?SettingsTacContract $tacContract = null;
    /** @var string[]|null */
    public ?array $tags = null;
    public ?SettingsTransactionFee $transactionFee = null;
    public ?string $type = null;
    /** @var SettingsSignedContracts[]|null */
    public ?array $signedContracts = null;

    public function fromArray(array $data): self
    {
        $this->avHandle = $data['av_handle'] ?? null;
        $this->claimsConfirmUrl = $data['claims_confirm_url'] ?? null;
        $this->claimsEmailSubject = $data['claims_email_subject'] ?? null;
        $this->claimsResellerName = $data['claims_reseller_name'] ?? null;
        $this->claimsSenderEmail = $data['claims_sender_email'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->defaultDomainBillingHandle = $data['default_domain_billing_handle'] ?? null;
        $this->defaultDomainResellerHandle = $data['default_domain_reseller_handle'] ?? null;
        $this->foaConfirmUrl = $data['foa_confirm_url'] ?? null;
        $this->foaEmailSubject = $data['foa_email_subject'] ?? null;
        $this->foaResellerName = $data['foa_reseller_name'] ?? null;
        $this->foaSenderEmail = $data['foa_sender_email'] ?? null;
        $this->foaTermsConditionsUrl = $data['foa_terms_conditions_url'] ?? null;
        $this->isAutoRenewEnabled = $data['is_auto_renew_enabled'] ?? null;
        $this->isCustomNsesEnabled = $data['is_custom_nses_enabled'] ?? null;
        $this->ive2CustomizedBody = $data['ive2_customized_body'] ?? null;
        $this->ive2EmailSubject = $data['ive2_email_subject'] ?? null;
        $this->iveConfirmUrl = $data['ive_confirm_url'] ?? null;
        $this->iveCustomizedBody = $data['ive_customized_body'] ?? null;
        $this->iveEmailSubject = $data['ive_email_subject'] ?? null;
        $this->iveResellerName = $data['ive_reseller_name'] ?? null;
        $this->iveSenderEmail = $data['ive_sender_email'] ?? null;
        $this->jurisdiction = $data['jurisdiction'] ?? null;
        $this->language = $data['language'] ?? null;
        $this->maximumCredit = $data['maximum_credit'] ?? null;
        $this->maximumCreditCardPayment = $data['maximum_credit_card_payment'] ?? null;
        $this->maximumIDealPayment = $data['maximum_i_deal_payment'] ?? null;
        $this->maximumWesternUnionPayment = $data['maximum_western_union_payment'] ?? null;
        $this->minimumPayment = $data['minimum_payment'] ?? null;
        $this->minimumWesternUnionPayment = $data['minimum_western_union_payment'] ?? null;
        $this->payMethods = $data['pay_methods'] ?? null;
        if (isset($data['payment_methods'])) {
            $this->paymentMethods = array_map(function ($method) {
                return (new SettingsPaymentMethods())->fromArray($method);
            }, $data['payment_methods']);
        }
        $this->preregisteredDomainPriorities = $data['preregistered_domain_priorities'] ?? null;
        if (isset($data['recurring_payment_methods'])) {
            $this->recurringPaymentMethods = array_map(function ($method) {
                return (new SettingsPaymentMethods())->fromArray($method);
            }, $data['recurring_payment_methods']);
        }
        $this->renewNotificationDays = $data['renew_notification_days'] ?? null;
        $this->renewNotificationEveryWeek = $data['renew_notification_every_week'] ?? null;
        if (isset($data['tac_contract'])) {
            $this->tacContract = (new SettingsTacContract())->fromArray($data['tac_contract']);
        }
        $this->tags = $data['tags'] ?? null;
        if (isset($data['transaction_fee'])) {
            $this->transactionFee = (new SettingsTransactionFee())->fromArray($data['transaction_fee']);
        }
        $this->type = $data['type'] ?? null;
        if (isset($data['signed_contracts'])) {
            $this->signedContracts = array_map(function ($contract) {
                return (new SettingsSignedContracts())->fromArray($contract);
            }, $data['signed_contracts']);
        }
        return $this;
    }

    public function toArray(): array
    {
        $data = array_filter([
            'av_handle' => $this->avHandle,
            'claims_confirm_url' => $this->claimsConfirmUrl,
            'claims_email_subject' => $this->claimsEmailSubject,
            'claims_reseller_name' => $this->claimsResellerName,
            'claims_sender_email' => $this->claimsSenderEmail,
            'currency' => $this->currency,
            'default_domain_billing_handle' => $this->defaultDomainBillingHandle,
            'default_domain_reseller_handle' => $this->defaultDomainResellerHandle,
            'foa_confirm_url' => $this->foaConfirmUrl,
            'foa_email_subject' => $this->foaEmailSubject,
            'foa_reseller_name' => $this->foaResellerName,
            'foa_sender_email' => $this->foaSenderEmail,
            'foa_terms_conditions_url' => $this->foaTermsConditionsUrl,
            'is_auto_renew_enabled' => $this->isAutoRenewEnabled,
            'is_custom_nses_enabled' => $this->isCustomNsesEnabled,
            'ive2_customized_body' => $this->ive2CustomizedBody,
            'ive2_email_subject' => $this->ive2EmailSubject,
            'ive_confirm_url' => $this->iveConfirmUrl,
            'ive_customized_body' => $this->iveCustomizedBody,
            'ive_email_subject' => $this->iveEmailSubject,
            'ive_reseller_name' => $this->iveResellerName,
            'ive_sender_email' => $this->iveSenderEmail,
            'jurisdiction' => $this->jurisdiction,
            'language' => $this->language,
            'maximum_credit' => $this->maximumCredit,
            'maximum_credit_card_payment' => $this->maximumCreditCardPayment,
            'maximum_i_deal_payment' => $this->maximumIDealPayment,
            'maximum_western_union_payment' => $this->maximumWesternUnionPayment,
            'minimum_payment' => $this->minimumPayment,
            'minimum_western_union_payment' => $this->minimumWesternUnionPayment,
            'pay_methods' => $this->payMethods,
            'preregistered_domain_priorities' => $this->preregisteredDomainPriorities,
            'renew_notification_days' => $this->renewNotificationDays,
            'renew_notification_every_week' => $this->renewNotificationEveryWeek,
            'tags' => $this->tags,
            'type' => $this->type,
        ]);

        if ($this->paymentMethods) {
            $data['payment_methods'] = array_map(function (SettingsPaymentMethods $method) {
                return $method->toArray();
            }, $this->paymentMethods);
        }

        if ($this->recurringPaymentMethods) {
            $data['recurring_payment_methods'] = array_map(function (SettingsPaymentMethods $method) {
                return $method->toArray();
            }, $this->recurringPaymentMethods);
        }

        if ($this->tacContract) {
            $data['tac_contract'] = $this->tacContract->toArray();
        }

        if ($this->transactionFee) {
            $data['transaction_fee'] = $this->transactionFee->toArray();
        }

        if ($this->signedContracts) {
            $data['signed_contracts'] = array_map(function (SettingsSignedContracts $contract) {
                return $contract->toArray();
            }, $this->signedContracts);
        }

        return $data;
    }
}
