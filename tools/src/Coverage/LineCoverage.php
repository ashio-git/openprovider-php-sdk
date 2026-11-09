<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Coverage;

/**
 * Line coverage read from a Clover report: executable statements and covered ones, per top-level
 * directory under the project root (`src`, `tools`) and in total. Each file's own `<metrics>` is
 * used, never the class or project totals, so the sum is exactly the files listed.
 */
final class LineCoverage
{
    /**
     * @param array<string, array{int, int}> $byArea top-level directory => [statements, covered]
     */
    private function __construct(
        public readonly array $byArea,
        public readonly int $statements,
        public readonly int $covered,
    ) {
    }

    /**
     * @throws \UnexpectedValueException when the XML is not a Clover report with executable lines
     */
    public static function fromClover(string $xml, string $root): self
    {
        $document = new \DOMDocument();
        if ($xml === '' || !@$document->loadXML($xml, LIBXML_NONET) || $document->documentElement?->nodeName !== 'coverage') {
            throw new \UnexpectedValueException('The report is not a Clover report');
        }

        $root = rtrim($root, '/') . '/';
        $byArea = [];
        foreach ((new \DOMXPath($document))->query('//file') ?: [] as $file) {
            if (!$file instanceof \DOMElement) {
                continue;
            }
            $name = $file->getAttribute('name');
            $metrics = null;
            foreach ($file->childNodes as $child) {
                if ($child instanceof \DOMElement && $child->nodeName === 'metrics') {
                    $metrics = $child;
                }
            }
            if ($metrics === null) {
                throw new \UnexpectedValueException(sprintf('%s has no metrics', $name));
            }
            $statements = self::count($metrics, 'statements', $name);
            $covered = self::count($metrics, 'coveredstatements', $name);
            if ($statements < 0 || $covered < 0 || $covered > $statements) {
                throw new \UnexpectedValueException(sprintf('%s covers %d of %d statements', $name, $covered, $statements));
            }
            if ($statements === 0) {
                continue;
            }
            $area = str_starts_with($name, $root) ? explode('/', substr($name, strlen($root)))[0] : $name;
            $byArea[$area] ??= [0, 0];
            $byArea[$area][0] += $statements;
            $byArea[$area][1] += $covered;
        }

        $statements = array_sum(array_column($byArea, 0));
        if ($statements === 0) {
            throw new \UnexpectedValueException('The report has no executable line');
        }

        return new self($byArea, $statements, array_sum(array_column($byArea, 1)));
    }

    public function percent(): float
    {
        return $this->covered * 100 / $this->statements;
    }

    /**
     * Whether at least $minimum percent of the statements are covered, compared in integers so
     * 190 of 200 meets 95 exactly.
     */
    public function meets(float $minimum): bool
    {
        return $this->covered * 10000 >= (int) round($minimum * 100.0) * $this->statements;
    }

    private static function count(\DOMElement $metrics, string $attribute, string $file): int
    {
        $value = $metrics->getAttribute($attribute);
        if (preg_match('/^-?\d+$/', $value) !== 1) {
            throw new \UnexpectedValueException(sprintf('%s: %s="%s" is not a count', $file, $attribute, $value));
        }

        return (int) $value;
    }
}
