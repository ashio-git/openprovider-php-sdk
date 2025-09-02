<?php

declare(strict_types=1);

namespace Ashio\Model;

class DnsPremiumDnsData
{
    public ?DnsSectigoData $sectigo = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->sectigo) {
            $data['sectigo'] = $this->sectigo->toArray();
        }
        return $data;
    }
}
