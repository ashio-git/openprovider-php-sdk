<?php

namespace Vexura\Model;

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

    public function fromArray(array $data): self
    {
        $this->recent_action = $data['recent_action'] ?? null;
        $this->message = $data['message'] ?? null;
        return $this;
    }
}