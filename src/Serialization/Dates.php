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

    private function __construct()
    {
    }

    /**
     * An API date as an immutable date: in Europe/Amsterdam, or in the offset the value carries.
     * Null and '' (absent) give null.
     *
     * @throws InvalidArgumentException when the value is none of the API's formats, or not a real
     *                                  date (2026-02-30)
     */
    public static function parse(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }
        $zone = new \DateTimeZone(self::TIMEZONE);
        foreach (self::FORMATS as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value, $zone);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date;
            }
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
