<?php

namespace Ashio\Model;

class Payment
{
    public ?int $id = null;
    public ?string $status = null;
    public ?string $creation_date = null;
    public ?string $payment_date = null;
    public ?string $confirmation_date = null;
    public ?string $method = null;
    public ?string $type = null;
    public ?array $amount = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
