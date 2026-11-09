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
    /** @var array<string, mixed>|null */
    public ?array $amount = null;

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
