<?php

declare(strict_types=1);

namespace Ashio\Model;

class ResendApproverEmailRequest
{
    public function __construct(
        public readonly ?int $id = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
        ], fn ($v) => $v !== null);
    }
}
