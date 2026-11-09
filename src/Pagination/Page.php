<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Pagination;

use Ashio\OpenProvider\Exception\UnexpectedResponseException;

/**
 * @template T
 */
final class Page
{
    /**
     * @param list<T>  $items
     * @param int|null $total how many items the whole listing has, when the API says
     */
    public function __construct(
        public readonly array $items,
        public readonly ?int $total = null,
    ) {
    }

    /**
     * Reads the API's list shape, `{results: [...], total: N}`.
     *
     * @return self<mixed>
     */
    public static function fromResults(mixed $data, int $httpStatus = 200): self
    {
        if (!is_array($data)) {
            throw new UnexpectedResponseException('A list response has no data object', $httpStatus);
        }
        $results = $data['results'] ?? [];
        if (!is_array($results) || !array_is_list($results)) {
            throw new UnexpectedResponseException('A list response has "results" that is not a list', $httpStatus);
        }

        return new self($results, isset($data['total']) && is_int($data['total']) ? $data['total'] : null);
    }
}
