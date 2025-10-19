<?php

namespace Ashio\Model;

class Zone
{
    public ?int $id = null;
    public ?string $name = null;
    public ?string $type = null;
    public ?bool $active = null;
    public ?string $creation_date = null;
    public ?string $modification_date = null;
    public ?bool $is_spamexperts_enabled = null;
    /** @var DnsRecord[] */
    public ?array $records = null;

    /**
     * @param array<string, mixed> $data
     */
    /**

     * @param array<string, mixed> $data

     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                if ($key === 'records' && is_array($value)) {
                    $this->records = array_map(fn ($recordData) => new DnsRecord($recordData), $value);
                } else {
                    $this->{$key} = $value;
                }
            }
        }
    }
}
