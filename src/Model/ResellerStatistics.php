<?php

declare(strict_types=1);

namespace Vexura\Model;

class ResellerStatistics
{
    public ?StatisticsCurrencyStatistics $currency = null;
    public ?StatisticsCustomerStatistics $customer = null;
    public ?StatisticsDnsStatistics $dns = null;
    public ?StatisticsDomainStatistics $domain = null;
    public ?int $level = null;
    public ?StatisticsLicenseStatistics $license = null;
    public ?StatisticsSpamExpertsStatistics $spamExperts = null;
    public ?StatisticsSslStatistics $ssl = null;

    public function fromArray(array $data): self
    {
        if (isset($data['currency'])) {
            $this->currency = (new StatisticsCurrencyStatistics())->fromArray($data['currency']);
        }
        if (isset($data['customer'])) {
            $this->customer = (new StatisticsCustomerStatistics())->fromArray($data['customer']);
        }
        if (isset($data['dns'])) {
            $this->dns = (new StatisticsDnsStatistics())->fromArray($data['dns']);
        }
        if (isset($data['domain'])) {
            $this->domain = (new StatisticsDomainStatistics())->fromArray($data['domain']);
        }
        $this->level = $data['level'] ?? null;
        if (isset($data['license'])) {
            $this->license = (new StatisticsLicenseStatistics())->fromArray($data['license']);
        }
        if (isset($data['spam_experts'])) {
            $this->spamExperts = (new StatisticsSpamExpertsStatistics())->fromArray($data['spam_experts']);
        }
        if (isset($data['ssl'])) {
            $this->ssl = (new StatisticsSslStatistics())->fromArray($data['ssl']);
        }
        return $this;
    }

    public function toArray(): array
    {
        $data = [
            'level' => $this->level,
        ];

        if ($this->currency) {
            $data['currency'] = $this->currency->toArray();
        }
        if ($this->customer) {
            $data['customer'] = $this->customer->toArray();
        }
        if ($this->dns) {
            $data['dns'] = $this->dns->toArray();
        }
        if ($this->domain) {
            $data['domain'] = $this->domain->toArray();
        }
        if ($this->license) {
            $data['license'] = $this->license->toArray();
        }
        if ($this->spamExperts) {
            $data['spam_experts'] = $this->spamExperts->toArray();
        }
        if ($this->ssl) {
            $data['ssl'] = $this->ssl->toArray();
        }

        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }
}
