<?php

declare(strict_types=1);

namespace Ashio\Model;

class SpamExpertUpdateDomainRequest
{
    public ?SpamExpertAliasUpdates $aliases = null;
    public ?bool $bundle = null;
    /** @var SpamExpertDestination[]|null */
    public ?array $destinations = null;
    public ?string $domainName = null;
    public ?SpamExpertProducts $products = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'bundle' => $this->bundle,
            'domain_name' => $this->domainName,
        ];
        if ($this->aliases) {
            $data['aliases'] = $this->aliases->toArray();
        }
        if ($this->destinations) {
            $data['destinations'] = array_map(static fn (SpamExpertDestination $d) => $d->toArray(), $this->destinations);
        }
        if ($this->products) {
            $data['products'] = $this->products->toArray();
        }

        return array_filter($data, static fn ($v) => $v !== null);
    }
}
