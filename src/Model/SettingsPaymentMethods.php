<?php

declare(strict_types=1);

namespace Ashio\Model;

class SettingsPaymentMethods
{
    public ?string $name = null;
    public ?SettingsPaymentMethodSettings $settings = null;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->name = $data['name'] ?? null;
        if (isset($data['settings'])) {
            $this->settings = (new SettingsPaymentMethodSettings())->fromArray($data['settings']);
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
        ];
        if ($this->settings) {
            $data['settings'] = $this->settings->toArray();
        }

        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }
}
