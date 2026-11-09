<?php

namespace Ashio\Model;

class SpamExpertProducts
{
    /**
     * @var bool|null
     */
    public $incoming;

    /**
     * @var bool|null
     */
    public $outgoing;

    /**
     * @var bool|null
     */
    public $archiving;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->incoming = $data['incoming'] ?? null;
        $this->outgoing = $data['outgoing'] ?? null;
        $this->archiving = $data['archiving'] ?? null;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'incoming' => $this->incoming,
            'outgoing' => $this->outgoing,
            'archiving' => $this->archiving,
        ], static fn ($v) => $v !== null);
    }
}
