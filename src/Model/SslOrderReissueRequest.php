<?php

declare(strict_types=1);

namespace Vexura\Model;

class SslOrderReissueRequest
{
    public ?string $approver_email = null;
    public ?string $csr = null;
    /** @var array<int,array{host_name:string,method:string}>|null */
    public ?array $domain_validation_methods = null;
    public ?bool $enable_dns_automation = null;
    /** @var string[]|null */
    public ?array $host_names = null;
    public ?string $organization_handle = null;
    public ?string $signature_hash_algorithm = null;
    public ?string $software_id = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [];
        foreach ([
            'approver_email','csr','domain_validation_methods','enable_dns_automation','host_names',
            'organization_handle','signature_hash_algorithm','software_id'
        ] as $prop) {
            $val = $this->$prop;
            if ($val !== null) {
                $data[$prop] = $val;
            }
        }
        return $data;
    }
}

