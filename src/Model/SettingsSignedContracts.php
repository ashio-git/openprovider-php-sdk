<?php

declare(strict_types=1);

namespace Ashio\Model;

class SettingsSignedContracts
{
    public ?string $contents = null;
    public ?int $id = null;
    public ?bool $isSigned = null;
    public ?string $modificationDate = null;
    public ?string $signOnDate = null;
    public ?string $title = null;
    public ?string $type = null;
    public ?int $versionId = null;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->contents = $data['contents'] ?? null;
        $this->id = $data['id'] ?? null;
        $this->isSigned = $data['is_signed'] ?? null;
        $this->modificationDate = $data['modification_date'] ?? null;
        $this->signOnDate = $data['sign_on_date'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->versionId = $data['version_id'] ?? null;

        return $this;
    }

    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        return array_filter([
            'contents' => $this->contents,
            'id' => $this->id,
            'is_signed' => $this->isSigned,
            'modification_date' => $this->modificationDate,
            'sign_on_date' => $this->signOnDate,
            'title' => $this->title,
            'type' => $this->type,
            'version_id' => $this->versionId,
        ], function ($value) {
            return $value !== null;
        });
    }
}
