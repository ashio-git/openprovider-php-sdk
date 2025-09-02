<?php

namespace Vexura\Model;

class DomainRegistryStatus
{
    public ?bool $is_changeable = null;
    public ?int $value = null;

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
        $this->is_changeable = $data['is_changeable'] ?? null;
        $this->value = $data['value'] ?? null;
        return $this;
    }
}
