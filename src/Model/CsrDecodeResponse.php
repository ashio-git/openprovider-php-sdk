<?php

declare(strict_types=1);

namespace Ashio\Model;

class CsrDecodeResponse
{
    public ?int $domain_names_count = null;
    public ?CsrPublicKey $public_key = null;
    public ?string $signature_hash_algorithm = null;
    public ?CsrSubject $subject = null;
    /**
     * @var string[]|null
     */
    public ?array $subject_alternative_name = null;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->domain_names_count = $data['domain_names_count'] ?? null;
        if (isset($data['public_key'])) {
            $this->public_key = (new CsrPublicKey())->fromArray($data['public_key']);
        }
        $this->signature_hash_algorithm = $data['signature_hash_algorithm'] ?? null;
        if (isset($data['subject'])) {
            $this->subject = (new CsrSubject())->fromArray($data['subject']);
        }
        $this->subject_alternative_name = $data['subject_alternative_name'] ?? null;

        return $this;
    }
}
