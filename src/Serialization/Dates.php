<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Serialization;

use Ashio\OpenProvider\Exception\InvalidArgumentException;

/**
 * The API's dates. Openprovider sends them as strings without an offset, in Europe/Amsterdam
 * (spec/behavior.json `dates`), and the models keep them exactly as received, so toArray() gives
 * back the original. Convert them here where you need a date:
 *
 *     $expires = Dates::parse($domain->expirationDate); // ?DateTimeImmutable, Europe/Amsterdam
 *     $body = ['scheduled_at' => Dates::format($when)];  // '2026-07-01 12:00:00'
 */
final class Dates
{
    public const TIMEZONE = 'Europe/Amsterdam';

    /**
     * The formats the specification's examples use: date and time (the hour with or without a
     * leading zero), a date alone, and ISO 8601 with an explicit offset.
     */
    private const FORMATS = ['!Y-m-d G:i:s', '!Y-m-d', \DateTimeInterface::ATOM];

    /** The API's "not set", which its examples send for deleted_at and similar fields. */
    private const ZERO_DATES = ['0000-00-00 00:00:00', '0000-00-00'];

    private function __construct()
    {
    }

    /**
     * An API date as an immutable date: in Europe/Amsterdam, or in the offset the value carries.
     * Null, '' and the zero date (0000-00-00, the API's "not set") give null.
     *
     * @throws InvalidArgumentException when the value is none of the API's formats, or not a real
     *                                  date (2026-02-30, or 02:30 on the night Amsterdam's clocks
     *                                  skip from 02:00 to 03:00)
     */
    public static function parse(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '' || in_array($value, self::ZERO_DATES, true)) {
            return null;
        }
        $zone = new \DateTimeZone(self::TIMEZONE);
        foreach (self::FORMATS as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value, $zone);
            // Since PHP 8.2 getLastErrors() is false when the parse had no warning (a day that
            // does not exist, such as 2026-02-30, is a warning) and no error.
            if ($date === false || \DateTimeImmutable::getLastErrors() !== false) {
                continue;
            }
            // A local time the clocks skip is moved an hour on without a warning: it no longer
            // reads back as written. A value with its own offset has no such gap.
            $readBack = [$date->format('Y-m-d H:i:s'), $date->format('Y-m-d G:i:s'), $date->format('Y-m-d')];
            if ($format !== \DateTimeInterface::ATOM && !in_array($value, $readBack, true)) {
                throw new InvalidArgumentException(sprintf('"%s" does not exist in %s: the clocks skip that hour', $value, self::TIMEZONE));
            }

            return $date;
        }

        throw new InvalidArgumentException(sprintf('"%s" is not a date the API sends (Y-m-d H:i:s, Y-m-d or ISO 8601)', $value));
    }

    /**
     * A date as the API expects it: converted to Europe/Amsterdam, `Y-m-d H:i:s`, or `Y-m-d`
     * without the time.
     */
    public static function format(\DateTimeInterface $date, bool $withTime = true): string
    {
        return \DateTimeImmutable::createFromInterface($date)
            ->setTimezone(new \DateTimeZone(self::TIMEZONE))
            ->format($withTime ? 'Y-m-d H:i:s' : 'Y-m-d');
    }
}
