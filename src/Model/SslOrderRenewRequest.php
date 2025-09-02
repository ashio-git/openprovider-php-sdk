<?php

declare(strict_types=1);

namespace Vexura\Model;

class SslOrderRenewRequest
{
    public ?bool $enable_dns_automation = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->enable_dns_automation !== null) {
            $data['enable_dns_automation'] = $this->enable_dns_automation;
        }
        return $data;
    }
}

