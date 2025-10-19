<?php

namespace Ashio\Model;

class SslProductLevelPrices
{
    /**
     * @var int|null
     */
    public $level;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var SslProductPrices[]|null
     */
    public $prices;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->level = $data['level'] ?? null;
        $this->name = $data['name'] ?? null;
        if (isset($data['prices'])) {
            $this->prices = array_map(function ($price) {
                return (new SslProductPrices())->fromArray($price);
            }, $data['prices']);
        }

        return $this;
    }
}
