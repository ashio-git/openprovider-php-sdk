<?php

declare(strict_types=1);

namespace Vexura\Model;

class CustomerFile
{
    /**
     * @var string|null A brief description of the file content.
     */
    public ?string $description = null;

    /**
     * @var string|null The type of the document (e.g., 'passport', 'id').
     */
    public ?string $docType = null;

    /**
     * @var string|null The link to the external file storage.
     */
    public ?string $path = null;

    /**
     * Populates the CustomerFile object from an array.
     *
     * @param array<string, mixed> $data The data to populate from.
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->description = $data['description'] ?? null;
        $this->docType = $data['doc_type'] ?? null;
        $this->path = $data['path'] ?? null;
        return $this;
    }

    /**
     * Converts the CustomerFile object to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'doc_type' => $this->docType,
            'path' => $this->path,
        ];
    }
}
