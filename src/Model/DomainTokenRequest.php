<?php

declare(strict_types=1);

namespace Vexura\Model;

class DomainTokenRequest
{
    public ?string $domain = null;
    public ?string $zoneProvider = null;

    public function toArray(): array
    {
        return array_filter([
            'domain' => $this->domain,
            'zone_provider' => $this->zoneProvider,
        ], function ($value) {
            return $value !== null;
        });
    }
}
