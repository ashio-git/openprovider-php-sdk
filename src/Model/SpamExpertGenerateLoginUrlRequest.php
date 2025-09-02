<?php

declare(strict_types=1);

namespace Ashio\Model;

class SpamExpertGenerateLoginUrlRequest
{
    public string $domainOrEmail;
    public ?bool $bundle = null;

    public function __construct(string $domainOrEmail)
    {
        $this->domainOrEmail = $domainOrEmail;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'domain_or_email' => $this->domainOrEmail,
        ];
        if ($this->bundle !== null) {
            $data['bundle'] = $this->bundle;
        }
        return $data;
    }
}
