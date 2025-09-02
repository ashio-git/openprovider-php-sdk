<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslOrderCreateRequest
{
    public int $product_id;
    public string $csr;
    /** @var string[] */
    public array $host_names = [];
    public int $period = 12;
    public ?string $approver_email = null;
    public ?string $organization_handle = null;
    public ?string $technical_handle = null;
    public ?bool $start_provision = null;

    public function __construct(int $productId, string $csr, array $hostNames, int $period = 12)
    {
        $this->product_id = $productId;
        $this->csr = $csr;
        $this->host_names = $hostNames;
        $this->period = $period;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'product_id' => $this->product_id,
            'csr' => $this->csr,
            'host_names' => $this->host_names,
            'period' => $this->period,
        ];
        if ($this->approver_email !== null) $data['approver_email'] = $this->approver_email;
        if ($this->organization_handle !== null) $data['organization_handle'] = $this->organization_handle;
        if ($this->technical_handle !== null) $data['technical_handle'] = $this->technical_handle;
        if ($this->start_provision !== null) $data['start_provision'] = $this->start_provision;
        return $data;
    }
}

