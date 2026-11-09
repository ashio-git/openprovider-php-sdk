<?php

declare(strict_types=1);

namespace Ashio\Model;

/**
 * Identity document type attached to a customer UIN (spec: domainUINDocumentType).
 */
class UINDocumentType
{
    public function __construct(
        public readonly ?string $description = null,
        public readonly ?string $doc_type = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            description: $data['description'] ?? null,
            doc_type: $data['doc_type'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'description' => $this->description,
            'doc_type' => $this->doc_type,
        ], fn ($v) => $v !== null);
    }
}
