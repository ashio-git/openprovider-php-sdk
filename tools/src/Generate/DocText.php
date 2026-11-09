<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Generate;

use Ashio\OpenProvider\Tools\Spec\Json;

/**
 * Spec text made safe for a PHPDoc block, shared by the generators.
 */
final class DocText
{
    /**
     * One doc line per line of $text; a line that starts with `@` is escaped so it cannot read
     * as a tag.
     *
     * @return list<string>
     */
    public static function lines(string $text): array
    {
        $lines = [];
        foreach (preg_split('/\R/u', trim($text)) ?: [] as $line) {
            $line = self::sanitize(rtrim($line));
            $lines[] = str_starts_with(ltrim($line), '@') ? '\\' . ltrim($line) : $line;
        }

        return $lines;
    }

    /**
     * $text on one line: runs of whitespace become one space.
     */
    public static function inline(string $text): string
    {
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return self::sanitize(implode(' ', $words === false ? [] : $words));
    }

    public static function sanitize(string $text): string
    {
        return str_replace('*/', '* /', $text);
    }

    /**
     * The developer portal's URL, from the merge's source record.
     *
     * @param array<array-key, mixed> $spec
     */
    public static function portalUrl(array $spec): ?string
    {
        $sources = isset($spec['x-openprovider-sources']) && is_array($spec['x-openprovider-sources']) ? $spec['x-openprovider-sources'] : [];
        $portal = isset($sources['portal']) && is_array($sources['portal']) ? $sources['portal'] : [];

        return Json::optionalString($portal, 'url');
    }

    /**
     * The tag that marks what only the developer portal documents.
     */
    public static function experimental(?string $portal): string
    {
        return sprintf(
            '@experimental Documented only by the developer portal%s; it may change without notice.',
            $portal === null ? '' : " ($portal)",
        );
    }
}
