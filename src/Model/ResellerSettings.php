<?php

declare(strict_types=1);

namespace Ashio\Model;

/**
 * Reseller Settings DTO
 * Represents reseller configuration and settings
 */
class ResellerSettings
{
    /**
     * Transaction fee settings
     * @var SettingsTransactionFee|null
     */
    public ?SettingsTransactionFee $transactionFee = null;

    /**
     * Default nameservers
     * @var array<int, string>|null
     */
    public ?array $defaultNameservers = null;

    /**
     * Default DNS template ID
     * @var int|null
     */
    public ?int $defaultDnsTemplateId = null;

    /**
     * Auto renew domains
     * @var bool|null
     */
    public ?bool $autoRenewDomains = null;

    /**
     * Auto renew SSL
     * @var bool|null
     */
    public ?bool $autoRenewSsl = null;

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
        if (isset($data['transaction_fee'])) {
            $this->transactionFee = (new SettingsTransactionFee())->fromArray($data['transaction_fee']);
        }

        $this->defaultNameservers = $data['default_nameservers'] ?? null;
        $this->defaultDnsTemplateId = $data['default_dns_template_id'] ?? null;
        $this->autoRenewDomains = $data['auto_renew_domains'] ?? null;
        $this->autoRenewSsl = $data['auto_renew_ssl'] ?? null;

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
            'default_nameservers' => $this->defaultNameservers,
            'default_dns_template_id' => $this->defaultDnsTemplateId,
            'auto_renew_domains' => $this->autoRenewDomains,
            'auto_renew_ssl' => $this->autoRenewSsl,
        ];

        if ($this->transactionFee !== null) {
            $data['transaction_fee'] = $this->transactionFee->toArray();
        }

        return array_filter($data, static fn ($v) => $v !== null);
    }
}
