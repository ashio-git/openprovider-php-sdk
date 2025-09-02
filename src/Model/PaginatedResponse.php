<?php

declare(strict_types=1);

namespace Vexura\Model;

/**
 * @template T
 */
class PaginatedResponse
{
    /**
     * Total results count
     * @var int
     */
    public int $total = 0;

    /**
     * Results array
     * @var array
     * @psalm-var array<int, T>
     * @phpstan-var array<int, T>
     */
    public array $results = [];

    /**
     * @param int $total
     * @param array $results
     */
    public function __construct(int $total = 0, array $results = [])
    {
        $this->total = $total;
        $this->results = $results;
    }
}

