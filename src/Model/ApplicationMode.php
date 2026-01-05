<?php

declare(strict_types=1);

namespace Ashio\Model;

class ApplicationMode
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $title = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            title: $data['title'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'title' => $this->title,
        ], fn($v) => $v !== null);
    }
}