<?php

namespace Vexura\Model;

class TldPrices
{
    public ?TldPrice $create_price = null;
    public ?TldPrice $domicile_price = null;
    public ?int $max_period = null;
    public ?int $min_period = null;
    public ?TldPrice $renew_price = null;
    public ?TldPrice $reseller_price = null;
    public ?TldPrice $restore_price = null;
    public ?TldPrice $setup_price = null;
    public ?TldPrice $soft_restore_price = null;
    public ?TldPrice $trade_price = null;
    public ?TldPrice $transfer_price = null;
    public ?TldPrice $update_price = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                if (is_array($value)) {
                    if (str_ends_with($key, '_price')) {
                        $this->{$key} = new TldPrice($value);
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
