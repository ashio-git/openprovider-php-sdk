<?php

namespace Vexura\Model;

class TldPrice
{
    public ?array $product = null;
    public ?array $reseller = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
