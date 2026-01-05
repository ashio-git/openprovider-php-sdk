<?php

declare(strict_types=1);

namespace Ashio\Model;

class UpdatePleskLicenseRequest
{
    public function __construct(
        /** @var string[]|null */
        public readonly ?array $attached_keys = null,
        public readonly ?string $comment = null,
        public readonly ?string $ip_address_binding = null,
        /** @var string[]|null */
        public readonly ?array $items = null,
        public readonly ?int $key_id = null,
        public readonly ?int $parent_key_id = null,
        public readonly ?int $period = null,
        public readonly ?bool $restrict_ip_binding = null,
        public readonly ?bool $suspended = null,
        public readonly ?string $title = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            attached_keys: $data['attached_keys'] ?? null,
            comment: $data['comment'] ?? null,
            ip_address_binding: $data['ip_address_binding'] ?? null,
            items: $data['items'] ?? null,
            key_id: $data['key_id'] ?? null,
            parent_key_id: $data['parent_key_id'] ?? null,
            period: $data['period'] ?? null,
            restrict_ip_binding: $data['restrict_ip_binding'] ?? null,
            suspended: $data['suspended'] ?? null,
            title: $data['title'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'attached_keys' => $this->attached_keys,
            'comment' => $this->comment,
            'ip_address_binding' => $this->ip_address_binding,
            'items' => $this->items,
            'key_id' => $this->key_id,
            'parent_key_id' => $this->parent_key_id,
            'period' => $this->period,
            'restrict_ip_binding' => $this->restrict_ip_binding,
            'suspended' => $this->suspended,
            'title' => $this->title,
        ], fn($v) => $v !== null);
    }
}