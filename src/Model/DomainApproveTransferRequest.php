<?php

declare(strict_types=1);

namespace Ashio\Model;

class DomainApproveTransferRequest
{
    public ?int $approve = null;
    public ?string $authCode = null;
    public ?DomainName $domain = null;
    public ?string $registrarTag = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'approve' => $this->approve,
            'auth_code' => $this->authCode,
            'registrar_tag' => $this->registrarTag,
        ];
        if ($this->domain) {
            $data['domain'] = $this->domain->toArray();
        }

        return array_filter($data, static fn ($v) => $v !== null);
    }
}
