<?php

namespace Vexura\Model;

class DomainRegistryStatuses
{
    /** @var array<string,DomainRegistryStatus> */
    public array $statuses = [];

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
            $this->statuses[$key] = (new DomainRegistryStatus())->fromArray($value);
        }
        return $this;
    }
}
