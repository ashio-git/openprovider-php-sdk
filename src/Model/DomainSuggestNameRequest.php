<?php

declare(strict_types=1);

namespace Vexura\Model;

class DomainSuggestNameRequest
{
    public ?string $language = null;
    public ?int $limit = null;
    public ?string $name = null;
    public ?string $provider = null;
    public ?bool $sensitive = null;
    /** @var string[]|null */
    public ?array $tlds = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'language' => $this->language,
            'limit' => $this->limit,
            'name' => $this->name,
            'provider' => $this->provider,
            'sensitive' => $this->sensitive,
            'tlds' => $this->tlds,
        ], static fn($v) => $v !== null);
    }
}
