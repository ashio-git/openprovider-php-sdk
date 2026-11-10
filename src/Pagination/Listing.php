<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Pagination;

use Ashio\OpenProvider\Serialization\Model;

/**
 * One page of a listing: the `data` of every list operation that takes `limit` and `offset`
 * (ListDomains, ListZones, ListInvoices, …), and of ListTags, which answers in a single page. The
 * generated models implement it, so code can read any listing the same way:
 *
 *     $page = $openProvider->dns()->listZones(limit: 50)->data;
 *     foreach ($page->items() as $zone) { … } // ZoneZone
 *     $page->total();                          // across all pages
 *
 * Paginator::all() walks every page for you.
 *
 * @template-covariant T of Model
 */
interface Listing extends Model
{
    /**
     * This page's items, in the order the API sent them; a page without `results` is empty.
     *
     * @return list<T>
     */
    public function items(): array;

    /**
     * The number of items across all pages, or null when the API does not send it.
     */
    public function total(): ?int;
}
