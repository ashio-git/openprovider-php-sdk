<?php

declare(strict_types=1);

namespace Ashio\Model;

class DnsSectigoData
{
    public ?bool $autorenew = null;
    public ?string $orderDate = null;
    public ?string $renewalDate = null;
    public ?bool $secured = null;
    public ?int $websiteId = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'autorenew' => $this->autorenew,
            'order_date' => $this->orderDate,
            'renewal_date' => $this->renewalDate,
            'secured' => $this->secured,
            'website_id' => $this->websiteId,
        ], static fn ($v) => $v !== null);
    }
}
