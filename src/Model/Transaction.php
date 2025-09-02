<?php

namespace Ashio\Model;

class Transaction
{
    public ?int $reference_id = null;
    public ?string $action = null;
    public ?string $creation_date = null;
    public ?string $subject = null;
    public ?array $total = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
