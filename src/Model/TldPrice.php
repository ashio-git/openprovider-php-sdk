<?php

namespace Ashio\Model;

class TldPrice
{
    /** @var array<string, mixed>|null */
    public ?array $product = null;
    /** @var array<string, mixed>|null */
    public ?array $reseller = null;

    /**

     * @param array<string, mixed> $data

     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
