<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Pagination;

use Ashio\OpenProvider\Exception\InvalidArgumentException;
use Ashio\OpenProvider\Exception\UnexpectedResponseException;
use Ashio\OpenProvider\Pagination\Page;
use Ashio\OpenProvider\Pagination\Paginator;
use PHPUnit\Framework\TestCase;

final class PaginatorTest extends TestCase
{
    public function testWalksPagesUntilAShortPage(): void
    {
        $calls = [];
        $items = iterator_to_array(Paginator::iterate(
            static function (int $limit, int $offset) use (&$calls): Page {
                $calls[] = [$limit, $offset];

                return new Page(array_slice(range(1, 7), $offset, $limit), null);
            },
            pageSize: 3,
        ), false);

        self::assertSame([1, 2, 3, 4, 5, 6, 7], $items);
        self::assertSame([[3, 0], [3, 3], [3, 6]], $calls);
    }

    public function testStopsAtTheTotalWithoutAnExtraCall(): void
    {
        $calls = 0;
        $items = iterator_to_array(Paginator::iterate(
            static function (int $limit, int $offset) use (&$calls): Page {
                ++$calls;

                return new Page(array_slice(range(1, 6), $offset, $limit), 6);
            },
            pageSize: 3,
        ), false);

        self::assertCount(6, $items);
        self::assertSame(2, $calls);
    }

    public function testThePageSizeNeverExceedsTheEndpointMaximum(): void
    {
        $limits = [];
        iterator_to_array(Paginator::iterate(
            static function (int $limit, int $offset) use (&$limits): Page {
                $limits[] = $limit;

                return new Page([], null);
            },
            pageSize: 5000,
            maxLimit: 500,
        ));

        self::assertSame([500], $limits);
    }

    public function testAShortPageDoesNotEndTheWalkWhileTheTotalSaysMoreRemain(): void
    {
        // An endpoint that caps pages below the limit sent must not cut the listing short.
        $offsets = [];
        $items = iterator_to_array(Paginator::iterate(
            static function (int $limit, int $offset) use (&$offsets): Page {
                $offsets[] = $offset;

                return new Page(array_slice(range(1, 250), $offset, min($limit, 100)), 250);
            },
            pageSize: 1000,
        ), false);

        self::assertCount(250, $items);
        self::assertSame([0, 100, 200], $offsets);
    }

    public function testAnEmptyPageEndsTheWalkEvenIfTheTotalSaysOtherwise(): void
    {
        $calls = 0;
        $items = iterator_to_array(Paginator::iterate(static function () use (&$calls): Page {
            ++$calls;

            return new Page([], 100);
        }));

        self::assertSame([], $items);
        self::assertSame(1, $calls);
    }

    public function testAPageOfOneAndAMaximumOfOneAreValid(): void
    {
        $calls = [];
        $items = iterator_to_array(Paginator::iterate(
            static function (int $limit, int $offset) use (&$calls): Page {
                $calls[] = [$limit, $offset];

                return new Page(array_slice([1, 2], $offset, $limit), null);
            },
            pageSize: 1,
            maxLimit: 1,
        ), false);

        self::assertSame([1, 2], $items);
        self::assertSame([[1, 0], [1, 1], [1, 2]], $calls);
    }

    public function testRejectsAMaximumBelowOne(): void
    {
        $this->expectException(InvalidArgumentException::class);

        iterator_to_array(Paginator::iterate(static fn (): Page => new Page([], null), maxLimit: 0));
    }

    public function testAPageReadsResultsAndAnIntegerTotalOnly(): void
    {
        $page = Page::fromResults(['results' => [1, 2], 'total' => '9']);

        self::assertSame([1, 2], $page->items);
        self::assertNull($page->total);
        self::assertSame(9, Page::fromResults(['results' => [], 'total' => 9])->total);
        self::assertSame([], Page::fromResults([])->items);
    }

    public function testAnUnreadablePageCarriesTheHttpStatus(): void
    {
        try {
            Page::fromResults('x');
            self::fail('no exception');
        } catch (UnexpectedResponseException $e) {
            self::assertSame(200, $e->httpStatus);
            self::assertSame(0, $e->getCode());
        }

        try {
            Page::fromResults(['results' => ['a' => 1]], 206);
            self::fail('no exception');
        } catch (UnexpectedResponseException $e) {
            self::assertSame(206, $e->httpStatus);
        }
    }

    public function testRejectsAPageSizeBelowOne(): void
    {
        $this->expectException(InvalidArgumentException::class);

        iterator_to_array(Paginator::iterate(static fn (): Page => new Page([], null), pageSize: 0));
    }

    public function testReadsTheListShapeOfTheApi(): void
    {
        $page = Page::fromResults(['results' => [['id' => 1], ['id' => 2]], 'total' => 9]);

        self::assertSame([['id' => 1], ['id' => 2]], $page->items);
        self::assertSame(9, $page->total);
        self::assertSame([], Page::fromResults(['total' => 0])->items);
    }

    public function testRejectsDataThatIsNotAList(): void
    {
        $this->expectException(UnexpectedResponseException::class);

        Page::fromResults(['results' => 'x']);
    }
}
