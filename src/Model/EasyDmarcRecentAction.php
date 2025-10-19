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
}
