<?php

declare(strict_types=1);

namespace Ashio\Model;

class ResellerAdditionalData
{
    public function __construct(
        public readonly ?string $cif_nif_number = null,
        public readonly ?string $gstin = null,
        public readonly ?string $internal_reference = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cif_nif_number: $data['cif_nif_number'] ?? null,
            gstin: $data['gstin'] ?? null,
            internal_reference: $data['internal_reference'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'cif_nif_number' => $this->cif_nif_number,
            'gstin' => $this->gstin,
            'internal_reference' => $this->internal_reference,
        ], fn($v) => $v !== null);
    }
}