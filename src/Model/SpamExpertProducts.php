<?php

namespace Vexura\Model;

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

    public function fromArray(array $data): self
    {
        $this->incoming = $data['incoming'] ?? null;
        $this->outgoing = $data['outgoing'] ?? null;
        $this->archiving = $data['archiving'] ?? null;
        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'incoming' => $this->incoming,
            'outgoing' => $this->outgoing,
            'archiving' => $this->archiving,
        ], static fn($v) => $v !== null);
    }
}