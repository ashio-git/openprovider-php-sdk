<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Serialization;

use Ashio\OpenProvider\Exception\InvalidArgumentException;
use Ashio\OpenProvider\Serialization\Dates;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The API sends dates as strings without an offset, in Europe/Amsterdam (spec/behavior.json
 * `dates`); models keep them as strings and Dates converts them where the caller needs a date.
 * The formats are the ones the specification's examples use.
 */
final class DatesTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function apiDates(): iterable
    {
        yield 'date and time, summer (CEST)' => ['2006-09-15 14:19:30', '2006-09-15T14:19:30+02:00'];
        yield 'date and time, winter (CET)' => ['2026-01-15 09:05:00', '2026-01-15T09:05:00+01:00'];
        yield 'hour without a leading zero' => ['2006-09-29 0:00:00', '2006-09-29T00:00:00+02:00'];
        yield 'date only, at midnight' => ['2019-03-30', '2019-03-30T00:00:00+01:00'];
        yield 'ISO 8601 keeps its own offset' => ['2018-01-30T14:38:45+01:00', '2018-01-30T14:38:45+01:00'];
        yield 'ISO 8601 in UTC' => ['2018-01-30T14:38:45+00:00', '2018-01-30T14:38:45+00:00'];
    }

    #[DataProvider('apiDates')]
    public function testParsesEveryFormatTheApiUses(string $value, string $atom): void
    {
        $date = Dates::parse($value);

        self::assertNotNull($date);
        self::assertSame($atom, $date->format(\DateTimeInterface::ATOM));
    }

    public function testAnAbsentDateIsNull(): void
    {
        self::assertNull(Dates::parse(null));
        self::assertNull(Dates::parse(''));
    }

    public function testTheZeroDateMeansNotSet(): void
    {
        // The specification's examples send it for deleted_at and verification_email_exp_date.
        self::assertNull(Dates::parse('0000-00-00 00:00:00'));
        self::assertNull(Dates::parse('0000-00-00'));
    }

    public function testATimeTheAmsterdamClockSkipsIsRefused(): void
    {
        // On 2026-03-29 the clocks go from 02:00 to 03:00 CEST: 02:30 never happens there.
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"2026-03-29 02:30:00" does not exist in Europe/Amsterdam: the clocks skip that hour');

        Dates::parse('2026-03-29 02:30:00');
    }

    public function testDatesWithoutAnOffsetAreInAmsterdam(): void
    {
        self::assertSame('Europe/Amsterdam', Dates::parse('2026-07-01 12:00:00')?->getTimezone()->getName());
        self::assertSame('Europe/Amsterdam', Dates::TIMEZONE);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function notDates(): iterable
    {
        yield 'a day that does not exist' => ['2026-02-30 10:00:00'];
        yield 'a month that does not exist' => ['2026-13-01'];
        yield 'an hour that does not exist' => ['2026-01-01 24:00:00'];
        yield 'text' => ['soon'];
        yield 'trailing text' => ['2026-01-01 10:00:00 tomorrow'];
        yield 'a time without a date' => ['10:00:00'];
        yield 'a month and day without leading zeros' => ['2026-7-1 12:00:00'];
        yield 'a date without leading zeros' => ['2026-7-1'];
    }

    #[DataProvider('notDates')]
    public function testAnythingElseIsRefused(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('"%s" is not a date the API sends', $value));

        Dates::parse($value);
    }

    public function testFormatWritesTheApiStringInAmsterdam(): void
    {
        $utc = new \DateTimeImmutable('2026-07-01 10:00:00', new \DateTimeZone('UTC'));

        self::assertSame('2026-07-01 12:00:00', Dates::format($utc));
        self::assertSame('2026-07-01', Dates::format($utc, withTime: false));
        self::assertSame('2026-01-02', Dates::format(new \DateTime('2026-01-01 23:30:00', new \DateTimeZone('UTC')), withTime: false), 'the date is the Amsterdam date');
    }

    public function testParseAndFormatRoundTrip(): void
    {
        // 2026-10-25 02:30 happens twice (the clocks go back); PHP reads the second, CET (+01:00).
        foreach (['2006-09-15 14:19:30', '2026-01-15 09:05:00', '2026-10-25 02:30:00', '2026-03-29 03:30:00'] as $value) {
            self::assertSame($value, Dates::format(Dates::parse($value) ?? throw new \LogicException($value)));
        }
    }
}
