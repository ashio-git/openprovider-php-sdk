<?php

declare(strict_types=1);

namespace Vexura\Model;

class LicenseResetHwidRequest
{
    public ?int $keyId = null;
    public ?string $product = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'key_id' => $this->keyId,
            'product' => $this->product,
        ], static fn($v) => $v !== null);
    }
}
