<?php

namespace Ashio\Model;

class RgpStatuses
{
    /**
     * @var string|null
     */
    public $add_period;

    /**
     * @var string|null
     */
    public $auto_renew_period;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->add_period = $data['add_period'] ?? null;
        $this->auto_renew_period = $data['auto_renew_period'] ?? null;

        return $this;
    }
}
