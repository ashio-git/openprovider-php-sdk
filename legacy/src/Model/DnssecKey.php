<?php

declare(strict_types=1);

namespace Ashio\Model;

class DnssecKey
{
    public function __construct(
        public readonly ?int $alg = null,
        public readonly ?int $flags = null,
        public readonly ?int $protocol = null,
        public readonly ?string $pub_key = null,
        public readonly ?int $readonly = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            alg: $data['alg'] ?? null,
            flags: $data['flags'] ?? null,
            protocol: $data['protocol'] ?? null,
            pub_key: $data['pub_key'] ?? null,
            readonly: $data['readonly'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'alg' => $this->alg,
            'flags' => $this->flags,
            'protocol' => $this->protocol,
            'pub_key' => $this->pub_key,
            'readonly' => $this->readonly,
        ], fn ($v) => $v !== null);
    }
}
