<?php

declare(strict_types=1);

namespace Vexura\Model;

class CreatePleskLicenseRequest
{
    public string $product = 'plesk';
    /** @var string[]|null */
    public ?array $items = null;
    public ?int $period = null;
    public ?string $title = null;

    public function __construct(?array $items = null, ?int $period = null, ?string $title = null)
    {
        $this->items = $items;
        $this->period = $period;
        $this->title = $title;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'product' => $this->product,
            'items' => $this->items,
            'period' => $this->period,
            'title' => $this->title,
        ], static fn($v) => $v !== null);
    }
}

