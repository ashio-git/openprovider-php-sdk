<?php

namespace Ashio\Model;

class SslProductSupportedSoftware
{
    /**
     * @var string|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $title;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? null;

        return $this;
    }
}
