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
