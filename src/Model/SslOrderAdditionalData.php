<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslOrderAdditionalData
{
    public ?string $dns = null;
    public ?string $dns_record = null;
    public ?string $dns_value = null;
    public ?string $domain = null;
    public ?string $file_content = null;
    public ?string $file_contents = null;
    public ?string $file_location = null;
    public ?string $file_name = null;
    public ?string $md5 = null;
    /** @var string[]|null */
    public ?array $old_cert_format = null;
    public ?string $sha1 = null;
    public ?string $sha256 = null;
    public ?string $token = null;
    public ?string $url = null;

    /**
     * @param array<string,mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
        return $this;
    }
}

