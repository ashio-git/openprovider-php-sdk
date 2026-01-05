<?php

declare(strict_types=1);

namespace Ashio\Model;

class ClientIpList
{
    public function __construct(
        /** @var string[]|null */
        public readonly ?array $allow = null,
        /** @var string[]|null */
        public readonly ?array $deny = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            allow: $data['allow'] ?? null,
            deny: $data['deny'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'allow' => $this->allow,
            'deny' => $this->deny,
        ], fn($v) => $v !== null);
    }
}