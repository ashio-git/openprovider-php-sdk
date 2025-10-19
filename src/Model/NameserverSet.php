<?php

declare(strict_types=1);

namespace Ashio\Model;

class NameserverSet
{
    public ?int $id = null;
    public ?string $ip = null;
    public ?string $ip6 = null;
    public ?string $name = null;
    public ?int $seqNr = null;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->ip = $data['ip'] ?? null;
        $this->ip6 = $data['ip6'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->seqNr = $data['seq_nr'] ?? null;

        return $this;
    }

    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'ip' => $this->ip,
            'ip6' => $this->ip6,
            'name' => $this->name,
            'seq_nr' => $this->seqNr,
        ], function ($value) {
            return $value !== null;
        });
    }
}
