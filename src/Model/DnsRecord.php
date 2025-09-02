<?php

namespace Vexura\Model;

class DnsRecord
{
    public ?string $name = null;
    public ?string $value = null;
    public ?string $type = null;
    public ?int $ttl = null;
    public ?int $prio = null;
    public ?string $creation_date = null;
    public ?string $modification_date = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
