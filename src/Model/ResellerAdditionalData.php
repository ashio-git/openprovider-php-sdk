<?php

declare(strict_types=1);

namespace Ashio\Model;

class ResellerAdditionalData
{
    public ?string $cifNifNumber = null;
    public ?string $gstin = null;
    public ?string $internalReference = null;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->cifNifNumber = $data['cif_nif_number'] ?? null;
        $this->gstin = $data['gstin'] ?? null;
        $this->internalReference = $data['internal_reference'] ?? null;

        return $this;
    }

    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        return array_filter([
            'cif_nif_number' => $this->cifNifNumber,
            'gstin' => $this->gstin,
            'internal_reference' => $this->internalReference,
        ], function ($value) {
            return $value !== null;
        });
    }
}
