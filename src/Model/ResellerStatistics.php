<?php

declare(strict_types=1);

namespace Ashio\Model;

/**
 * Reseller Statistics DTO
 * Represents reseller usage statistics
 */
class ResellerStatistics
{
    /**
     * Total domains
     * @var int|null
     */
    public ?int $totalDomains = null;

    /**
     * Active domains
     * @var int|null
     */
    public ?int $activeDomains = null;

    /**
     * Pending domains
     * @var int|null
     */
    public ?int $pendingDomains = null;

    /**
     * Total SSL certificates
     * @var int|null
     */
    public ?int $totalSsl = null;

    /**
     * Active SSL certificates
     * @var int|null
     */
    public ?int $activeSsl = null;

    /**
     * Total licenses
     * @var int|null
     */
    public ?int $totalLicenses = null;

    /**
     * Active licenses
     * @var int|null
     */
    public ?int $activeLicenses = null;

    /**
     * Domain statistics by status
     * @var StatisticsDomainStatisticsByStatus|null
     */
    public ?StatisticsDomainStatisticsByStatus $domainStatisticsByStatus = null;

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
        $this->totalDomains = $data['total_domains'] ?? null;
        $this->activeDomains = $data['active_domains'] ?? null;
        $this->pendingDomains = $data['pending_domains'] ?? null;
        $this->totalSsl = $data['total_ssl'] ?? null;
        $this->activeSsl = $data['active_ssl'] ?? null;
        $this->totalLicenses = $data['total_licenses'] ?? null;
        $this->activeLicenses = $data['active_licenses'] ?? null;

        if (isset($data['domain_statistics_by_status'])) {
            $this->domainStatisticsByStatus = (new StatisticsDomainStatisticsByStatus())->fromArray($data['domain_statistics_by_status']);
        }

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
            'total_domains' => $this->totalDomains,
            'active_domains' => $this->activeDomains,
            'pending_domains' => $this->pendingDomains,
            'total_ssl' => $this->totalSsl,
            'active_ssl' => $this->activeSsl,
            'total_licenses' => $this->totalLicenses,
            'active_licenses' => $this->activeLicenses,
        ];

        if ($this->domainStatisticsByStatus !== null) {
            $data['domain_statistics_by_status'] = $this->domainStatisticsByStatus->toArray();
        }

        return array_filter($data, static fn ($v) => $v !== null);
    }
}
