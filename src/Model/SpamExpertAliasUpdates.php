<?php

declare(strict_types=1);

namespace Ashio\Model;

class SpamExpertAliasUpdates
{
    /** @var string[]|null */
    public ?array $add = null;
    /** @var string[]|null */
    public ?array $remove = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'add' => $this->add,
            'remove' => $this->remove,
        ], static fn ($v) => $v !== null);
    }
}
