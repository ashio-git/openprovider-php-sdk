<?php

namespace Ashio\Model;

class Invoice
{
    public ?int $id = null;
    public ?string $invoice_number = null;
    public ?string $creation_date = null;
    public ?array $amount = null;
    public ?string $invoice_url = null;
    public ?string $attachment_url = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
