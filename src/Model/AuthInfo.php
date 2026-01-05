<?php

declare(strict_types=1);

namespace Ashio\Model;

class AuthInfo
{
    public function __construct(
        public readonly ?string $pw = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            pw: $data['pw'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'pw' => $this->pw,
        ], fn($v) => $v !== null);
    }
}