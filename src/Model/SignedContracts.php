<?php

declare(strict_types=1);

namespace Ashio\Model;

class SignedContracts
{
    public function __construct(
        public readonly ?string $contents = null,
        public readonly ?int $id = null,
        public readonly ?bool $is_signed = null,
        public readonly ?string $modification_date = null,
        public readonly ?string $sign_on_date = null,
        public readonly ?string $title = null,
        public readonly ?string $type = null,
        public readonly ?int $version_id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contents: $data['contents'] ?? null,
            id: $data['id'] ?? null,
            is_signed: $data['is_signed'] ?? null,
            modification_date: $data['modification_date'] ?? null,
            sign_on_date: $data['sign_on_date'] ?? null,
            title: $data['title'] ?? null,
            type: $data['type'] ?? null,
            version_id: $data['version_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'contents' => $this->contents,
            'id' => $this->id,
            'is_signed' => $this->is_signed,
            'modification_date' => $this->modification_date,
            'sign_on_date' => $this->sign_on_date,
            'title' => $this->title,
            'type' => $this->type,
            'version_id' => $this->version_id,
        ], fn($v) => $v !== null);
    }
}