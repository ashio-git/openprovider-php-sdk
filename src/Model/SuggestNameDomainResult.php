<?php

namespace Ashio\Model;

class SuggestNameDomainResult
{
    /**
     * @var string|null
     */
    public $domain;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $tld;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->domain = $data['domain'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->tld = $data['tld'] ?? null;

        return $this;
    }
}
