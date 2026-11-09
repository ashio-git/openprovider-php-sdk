<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Pagination;

use Ashio\OpenProvider\Exception\InvalidArgumentException;

/**
 * Walks a limit/offset listing page by page. The page size is capped at the endpoint's
 * documented maximum (1000 for domains and SSL orders, 500 for DNS zones and records).
 */
final class Paginator
{
    /**
     * @template T
     *
     * @param \Closure(int, int): Page<T> $fetch receives (limit, offset)
     *
     * @return \Generator<int, T, mixed, void>
     */
    public static function iterate(\Closure $fetch, int $pageSize = 100, ?int $maxLimit = null): \Generator
    {
        if ($pageSize < 1 || ($maxLimit !== null && $maxLimit < 1)) {
            throw new InvalidArgumentException('pageSize and maxLimit must be at least 1');
        }

        return self::walk($fetch, $maxLimit === null ? $pageSize : min($pageSize, $maxLimit));
    }

    /**
     * @template T
     *
     * @param \Closure(int, int): Page<T> $fetch
     *
     * @return \Generator<int, T, mixed, void>
     */
    private static function walk(\Closure $fetch, int $limit): \Generator
    {
        $offset = 0;
        while (true) {
            $page = $fetch($limit, $offset);
            foreach ($page->items as $item) {
                yield $item;
            }
            $offset += count($page->items);
            if (count($page->items) < $limit || ($page->total !== null && $offset >= $page->total)) {
                return;
            }
        }
    }
}
