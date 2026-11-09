<?php

declare(strict_types=1);

namespace Ashio\Model;

class DomainTryAgainLastOperationRequest
{
    public ?string $authCode = null;
    public ?DomainName $domain = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'auth_code' => $this->authCode,
        ];
        if ($this->domain) {
            $data['domain'] = $this->domain->toArray();
        }

        return array_filter($data, static fn ($v) => $v !== null);
    }
}
