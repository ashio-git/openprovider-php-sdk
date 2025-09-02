<?php

namespace Vexura\Model;

class Tld
{
    public ?string $name = null;
    public ?string $status = null;
    public ?int $min_period = null;
    public ?int $max_period = null;
    public ?bool $is_private_whois_allowed = null;
    public ?TldPrices $prices = null;
    public ?TldDescription $description = null;
    public ?array $restrictions = null;
    public ?int $usage_count = null;
    /** @var int[]|null */
    public ?array $dnssec_algorithms = null;
    public ?bool $dnssec_allowed = null;
    public ?bool $dnssec_digests_allowed = null;
    public ?bool $dnssec_keys_allowed = null;
    public ?int $dnssec_max_records_amount = null;
    public ?bool $domicile_available = null;
    public ?bool $is_auth_code_available = null;
    public ?bool $is_auth_code_changeable = null;
    public ?bool $is_auth_code_requested = null;
    public ?bool $is_trade_auth_code_required = null;
    public ?bool $is_transfer_auth_code_required = null;
    /** @var TldApplicationMode[]|null */
    public ?array $supported_application_mode = null;
    /** @var TldIdnScript[]|null */
    public ?array $supported_idn_scripts = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                if (is_array($value)) {
                    if ($key === 'prices') {
                        $this->{$key} = new TldPrices($value);
                    } elseif ($key === 'description') {
                        $this->{$key} = new TldDescription($value);
                    } elseif ($key === 'supported_application_mode') {
                        $this->{$key} = array_map(static fn($row) => (new TldApplicationMode())->fromArray($row), $value);
                    } elseif ($key === 'supported_idn_scripts') {
                        $this->{$key} = array_map(static fn($row) => (new TldIdnScript())->fromArray($row), $value);
                    } else {
                        $this->{$key} = $value;
                    }
                } else {
                    $this->{$key} = $value;
                }
            }
        }
    }
}
