<?php

namespace Ashio\Model;

class EasyDmarcRecentAction
{
    /**
     * @var string|null
     */
    public $recent_action;

    /**
     * @var string|null
     */
    public $message;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->recent_action = $data['recent_action'] ?? null;
        $this->message = $data['message'] ?? null;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'recent_action' => $this->recent_action,
            'message' => $this->message,
        ], static fn ($v) => $v !== null);
    }
}
