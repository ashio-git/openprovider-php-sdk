<?php

namespace Vexura\Model;

class License
{
    public ?int $key_id = null;
    public ?string $key_number = null;
    public ?string $product = null;
    public ?string $status = null;
    public ?string $title = null;
    public ?string $order_date = null;
    public ?string $expiration_date = null;
    public ?int $period = null;
    public ?string $ip_address_binding = null;
    public ?array $items = null;

    public function __construct(array $data = [])
    {
        if ($data) {
            $this->fromArray($data);
        }
    }

    /**
     * @param array<string,mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'key_id' => $this->key_id,
            'key_number' => $this->key_number,
            'product' => $this->product,
            'status' => $this->status,
            'title' => $this->title,
            'order_date' => $this->order_date,
            'expiration_date' => $this->expiration_date,
            'period' => $this->period,
            'ip_address_binding' => $this->ip_address_binding,
            'items' => $this->items,
        ], static fn($v) => $v !== null);
    }
}
