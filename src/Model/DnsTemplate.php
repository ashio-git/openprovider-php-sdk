<?php

declare(strict_types=1);

namespace Ashio\Model;

class DnsTemplate
{
    public ?int $id = null;
    public ?bool $isSpamexpertsEnabled = null;
    public ?string $name = null;
    /** @var DnsTemplateRecord[]|null */
    public ?array $records = null;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->isSpamexpertsEnabled = $data['is_spamexperts_enabled'] ?? null;
        $this->name = $data['name'] ?? null;

        if (isset($data['records'])) {
            $this->records = array_map(function ($record) {
                return (new DnsTemplateRecord())->fromArray($record);
            }, $data['records']);
        }

        return $this;
    }

    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'is_spamexperts_enabled' => $this->isSpamexpertsEnabled,
            'name' => $this->name,
        ];

        if ($this->records) {
            $data['records'] = array_map(function (DnsTemplateRecord $record) {
                return $record->toArray();
            }, $this->records);
        }

        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }
}
