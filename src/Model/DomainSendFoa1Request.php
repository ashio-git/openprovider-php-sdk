<?php

declare(strict_types=1);

namespace Vexura\Model;

class DomainSendFoa1Request
{
    public ?DomainName $domain = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->domain) {
            $data['domain'] = $this->domain->toArray();
        }
        return $data;
    }
}
