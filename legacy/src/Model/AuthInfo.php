<?php

declare(strict_types=1);

namespace Ashio\Model;

class AuthInfo
{
    public function __construct(
        public readonly ?string $pw = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            pw: $data['pw'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'pw' => $this->pw,
        ], fn ($v) => $v !== null);
    }
}
