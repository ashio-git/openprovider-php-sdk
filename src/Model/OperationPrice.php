<?php

declare(strict_types=1);

namespace Ashio\Model;

class OperationPrice
{
    public function __construct(
        public readonly ?float $create = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            create: $data['create'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'create' => $this->create,
        ], fn ($v) => $v !== null);
    }
}
