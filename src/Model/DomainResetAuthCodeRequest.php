<?php

declare(strict_types=1);

namespace Ashio\Model;

class DomainResetAuthCodeRequest
{
    public ?string $authCodeType = null;
    public ?DomainName $domain = null;
    public ?string $sendingType = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'auth_code_type' => $this->authCodeType,
            'sending_type' => $this->sendingType,
        ];
        if ($this->domain) {
            $data['domain'] = $this->domain->toArray();
        }

        return array_filter($data, static fn ($v) => $v !== null);
    }
}
