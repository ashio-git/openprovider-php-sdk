<?php

declare(strict_types=1);

namespace Vexura\Model;

class UpdateVirtuozzoLicenseRequest
{
    public ?string $title = null;
    /** @var string[]|null */
    public ?array $items = null;
    public ?string $status = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'items' => $this->items,
            'status' => $this->status,
        ], static fn($v) => $v !== null);
    }
}

