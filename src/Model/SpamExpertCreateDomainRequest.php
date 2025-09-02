<?php

declare(strict_types=1);

namespace Ashio\Model;

class SpamExpertCreateDomainRequest
{
    /** @var string[]|null */
    public ?array $aliases = null;
    public ?bool $bundle = null;
    /** @var SpamExpertDestination[]|null */
    public ?array $destinations = null;
    public string $domainName;
    public ?SpamExpertProducts $products = null;

    public function __construct(string $domainName)
    {
        $this->domainName = $domainName;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'aliases' => $this->aliases,
            'bundle' => $this->bundle,
            'domain_name' => $this->domainName,
        ];
        if ($this->destinations) {
            $data['destinations'] = array_map(static fn(SpamExpertDestination $d) => $d->toArray(), $this->destinations);
        }
        if ($this->products) {
            $data['products'] = $this->products->toArray();
        }
        return array_filter($data, static fn($v) => $v !== null);
    }
}
