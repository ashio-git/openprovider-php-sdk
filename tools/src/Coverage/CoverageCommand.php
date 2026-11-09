<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Coverage;

/**
 * tools/coverage: prints the line coverage of a Clover report per top-level directory and in
 * total, then holds the total to the minimum of plan S4 (95% unless --min says otherwise). Exits
 * 0 at or above it, 1 below it, 2 on bad usage or an unreadable or invalid report.
 */
final class CoverageCommand
{
    public const MINIMUM = 95.0;

    private const USAGE = "Usage: tools/coverage <clover.xml> [--min=<percent>]\n";

    /**
     * @param list<string> $arguments
     * @param resource     $out
     * @param resource     $err
     */
    public static function run(array $arguments, string $root, $out, $err): int
    {
        $files = [];
        $minimum = self::MINIMUM;
        foreach ($arguments as $argument) {
            if (str_starts_with($argument, '--min=')) {
                $value = substr($argument, strlen('--min='));
                if (!is_numeric($value) || (float) $value < 0 || (float) $value > 100) {
                    fwrite($err, "--min must be a number from 0 to 100\n");

                    return 2;
                }
                $minimum = (float) $value;
            } elseif (str_starts_with($argument, '-')) {
                fwrite($err, self::USAGE);

                return 2;
            } else {
                $files[] = $argument;
            }
        }
        if (count($files) !== 1) {
            fwrite($err, self::USAGE);

            return 2;
        }

        $xml = is_file($files[0]) ? @file_get_contents($files[0]) : false;
        if ($xml === false) {
            fwrite($err, 'Cannot read ' . $files[0] . "\n");

            return 2;
        }

        try {
            $coverage = LineCoverage::fromClover($xml, $root);
        } catch (\UnexpectedValueException $e) {
            fwrite($err, $files[0] . ': ' . $e->getMessage() . "\n");

            return 2;
        }

        $areas = $coverage->byArea;
        ksort($areas, SORT_STRING);
        foreach ($areas as $area => [$statements, $covered]) {
            fwrite($out, self::line($area, $covered, $statements) . "\n");
        }
        fwrite($out, sprintf("%s, minimum %.2f%%\n", self::line('total', $coverage->covered, $coverage->statements), $minimum));

        if (!$coverage->meets($minimum)) {
            fwrite($err, sprintf("Line coverage %s%% is below the minimum of %.2f%%\n", self::percent($coverage->covered, $coverage->statements), $minimum));

            return 1;
        }

        return 0;
    }

    private static function line(string $label, int $covered, int $statements): string
    {
        return sprintf('%-8s%6s%% (%d/%d)', $label, self::percent($covered, $statements), $covered, $statements);
    }

    /**
     * Rounded down, so a total just under the minimum never prints as the minimum.
     */
    private static function percent(int $covered, int $statements): string
    {
        return sprintf('%.2f', intdiv($covered * 10000, $statements) / 100);
    }
}
