<?php

declare(strict_types=1);

namespace Ashio\Model;

class NameserverGroup
{
    public ?int $id = null;
    public ?string $nsGroup = null;
    public ?int $nsCount = null;
    public ?int $domainCount = null;
    /** @var NameserverSet[]|null */
    public ?array $nameServers = null;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->nsGroup = $data['ns_group'] ?? null;
        $this->nsCount = $data['ns_count'] ?? null;
        $this->domainCount = $data['domain_count'] ?? null;

        if (isset($data['name_servers'])) {
            $this->nameServers = array_map(function ($nameServer) {
                return (new NameserverSet())->fromArray($nameServer);
            }, $data['name_servers']);
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
            'ns_group' => $this->nsGroup,
            'ns_count' => $this->nsCount,
            'domain_count' => $this->domainCount,
        ];

        if ($this->nameServers) {
            $data['name_servers'] = array_map(function (NameserverSet $nameServer) {
                return $nameServer->toArray();
            }, $this->nameServers);
        }

        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }
}
