<?php

declare(strict_types=1);

namespace Ashio\Model;

class CustomerExtensionAdditionalData
{
    /**
     * @var CustomerExtensionData|null
     */
    public ?CustomerExtensionData $data = null;

    /**
     * @var string|null
     */
    public ?string $name = null;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        if (isset($data['data'])) {
            $this->data = (new CustomerExtensionData())->fromArray($data['data']);
        }
        $this->name = $data['name'] ?? null;

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

        if ($this->data) {
            $data['data'] = $this->data->toArray();
        }

        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }
}
