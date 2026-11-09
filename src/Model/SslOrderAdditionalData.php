<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslOrderAdditionalData
{
    public function __construct(
        public readonly ?string $dns = null,
        public readonly ?string $dns_record = null,
        public readonly ?string $dns_value = null,
        public readonly ?string $domain = null,
        public readonly ?string $file_content = null,
        public readonly ?string $file_contents = null,
        public readonly ?string $file_location = null,
        public readonly ?string $file_name = null,
        public readonly ?string $md5 = null,
        /** @var string[]|null */
        public readonly ?array $old_cert_format = null,
        public readonly ?string $sha1 = null,
        public readonly ?string $sha256 = null,
        public readonly ?string $token = null,
        public readonly ?string $url = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            dns: $data['dns'] ?? null,
            dns_record: $data['dns_record'] ?? null,
            dns_value: $data['dns_value'] ?? null,
            domain: $data['domain'] ?? null,
            file_content: $data['file_content'] ?? null,
            file_contents: $data['file_contents'] ?? null,
            file_location: $data['file_location'] ?? null,
            file_name: $data['file_name'] ?? null,
            md5: $data['md5'] ?? null,
            old_cert_format: $data['old_cert_format'] ?? null,
            sha1: $data['sha1'] ?? null,
            sha256: $data['sha256'] ?? null,
            token: $data['token'] ?? null,
            url: $data['url'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'dns' => $this->dns,
            'dns_record' => $this->dns_record,
            'dns_value' => $this->dns_value,
            'domain' => $this->domain,
            'file_content' => $this->file_content,
            'file_contents' => $this->file_contents,
            'file_location' => $this->file_location,
            'file_name' => $this->file_name,
            'md5' => $this->md5,
            'old_cert_format' => $this->old_cert_format,
            'sha1' => $this->sha1,
            'sha256' => $this->sha256,
            'token' => $this->token,
            'url' => $this->url,
        ], fn ($v) => $v !== null);
    }
}
