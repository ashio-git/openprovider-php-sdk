<?php

declare(strict_types=1);

namespace Ashio\Model;

class Description
{
    public function __construct(
        public readonly ?string $text = null,
        public readonly ?string $url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? null,
            url: $data['url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'text' => $this->text,
            'url' => $this->url,
        ], fn($v) => $v !== null);
    }
}