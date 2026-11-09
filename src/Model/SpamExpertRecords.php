<?php

namespace Ashio\Model;

class SpamExpertRecords
{
    /**
     * @var string[]|null
     */
    public $aliases;

    /**
     * @var SpamExpertDestination[]|null
     */
    public $destinations;

    /**
     * @var string[]|null
     */
    public $outgoing_user_ips;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->aliases = $data['aliases'] ?? null;
        $this->outgoing_user_ips = $data['outgoing_user_ips'] ?? null;

        if (isset($data['destinations'])) {
            $this->destinations = array_map(function ($destination) {
                return (new SpamExpertDestination())->fromArray($destination);
            }, $data['destinations']);
        }

        return $this;
    }
}
