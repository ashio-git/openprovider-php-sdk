<?php

namespace Ashio\Model;

class OperationPrice
{
    /**
     * @var float|null
     */
    public $create;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->create = $data['create'] ?? null;

        return $this;
    }
}
