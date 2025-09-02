<?php

namespace Vexura\Model;

class SpamExpertDestination
{
    /**
     * @var string|null
     */
    public $hostname;

    /**
     * @var int|null
     */
    public $port;

    public function fromArray(array $data): self
    {
        $this->hostname = $data['hostname'] ?? null;
        $this->port = $data['port'] ?? null;
        return $this;
    }
}