<?php

declare(strict_types=1);

namespace Ashio\Model;

class SettingsTacContract
{
    public ?bool $isSigned = null;
    public ?string $name = null;
    public ?string $signedAt = null;
    public ?string $type = null;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->isSigned = $data['is_signed'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->signedAt = $data['signed_at'] ?? null;
        $this->type = $data['type'] ?? null;

        return $this;
    }

    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        return array_filter([
            'is_signed' => $this->isSigned,
            'name' => $this->name,
            'signed_at' => $this->signedAt,
            'type' => $this->type,
        ], function ($value) {
            return $value !== null;
        });
    }
}
