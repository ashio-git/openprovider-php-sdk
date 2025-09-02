<?php

namespace Ashio\Model;

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
    public ?int $quarantinePeriod = null;
    public ?bool $tradeAvailable = null;
    public ?bool $transferAvailable = null;
    /** @var TldLevelPrice[]|null */
    public ?array $levelPrices = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'prices':
                    $this->prices = new TldPrices($value);
                    break;
                case 'description':
                    $this->description = new TldDescription($value);
                    break;
                case 'supported_application_mode':
                    $this->supported_application_mode = array_map(static fn($row) => (new TldApplicationMode())->fromArray($row), $value);
                    break;
                case 'supported_idn_scripts':
                    $this->supported_idn_scripts = array_map(static fn($row) => (new TldIdnScript())->fromArray($row), $value);
                    break;
                case 'level_prices':
                    $this->levelPrices = array_map(static fn($row) => (new TldLevelPrice())->fromArray($row), $value);
                    break;
                case 'quarantine_period':
                    $this->quarantinePeriod = $value;
                    break;
                case 'trade_available':
                    $this->tradeAvailable = $value;
                    break;
                case 'transfer_available':
                    $this->transferAvailable = $value;
                    break;
                default:
                    if (property_exists($this, $key)) {
                        $this->{$key} = $value;
                    }
                    break;
            }
        }
    }
}
