<?php

declare(strict_types=1);

namespace Ashio\Model;

class SslProductSupportedSoftware
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $title = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            title: $data['title'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'title' => $this->title,
        ], fn ($v) => $v !== null);
    }
}
