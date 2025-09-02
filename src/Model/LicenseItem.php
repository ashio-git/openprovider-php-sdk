<?php

declare(strict_types=1);

namespace Ashio\Model;

class LicenseItem
{
    public ?string $item = null;
    public ?string $product = null;
    /**
     * @var array<string,bool>|null
     */
    public ?array $sku_values = null;
    public ?string $title = null;

    /**
     * @param array<string,mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->item = $data['item'] ?? null;
        $this->product = $data['product'] ?? null;
        $this->sku_values = $data['sku_values'] ?? null;
        $this->title = $data['title'] ?? null;
        return $this;
    }
}

