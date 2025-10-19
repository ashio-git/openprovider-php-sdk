<?php

declare(strict_types=1);

namespace Ashio\Model;

class Nameserver
{
    public ?string $name = null;
    public ?string $ip = null;
    public ?string $ip6 = null;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->name = $data['name'] ?? null;
        $this->ip = $data['ip'] ?? null;
        $this->ip6 = $data['ip6'] ?? null;

        return $this;
    }

    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'ip' => $this->ip,
            'ip6' => $this->ip6,
        ], function ($value) {
            return $value !== null;
        });
    }
}
