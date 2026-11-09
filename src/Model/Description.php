<?php

declare(strict_types=1);

namespace Ashio\Model;

class Description
{
    public function __construct(
        public readonly ?string $text = null,
        public readonly ?string $url = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? null,
            url: $data['url'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'text' => $this->text,
            'url' => $this->url,
        ], fn ($v) => $v !== null);
    }
}
