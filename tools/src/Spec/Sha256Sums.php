<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * The `sha256sum` file format: `<64 hex>  <path>` per line, paths relative to its directory.
 * Rendering sorts by path, so `sha256sum -c` and diffs stay stable.
 */
final class Sha256Sums
{
    /**
     * @param array<string, string> $sums path => sha256
     */
    private function __construct(private readonly array $sums)
    {
    }

    public static function parse(string $contents): self
    {
        $sums = [];
        foreach (preg_split('/\R/', $contents) ?: [] as $number => $line) {
            if ($line === '') {
                continue;
            }
            if (preg_match('/^([0-9a-f]{64}) [ *](.+)$/', $line, $match) !== 1) {
                throw new SpecException(sprintf('SHA256SUMS line %d is malformed: %s', $number + 1, $line));
            }
            if (isset($sums[$match[2]])) {
                throw new SpecException(sprintf('SHA256SUMS lists %s twice', $match[2]));
            }
            $sums[$match[2]] = $match[1];
        }

        return new self($sums);
    }

    public function get(string $path): ?string
    {
        return $this->sums[$path] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->sums;
    }

    public function with(string $path, string $sha256): self
    {
        if (preg_match('/^[0-9a-f]{64}$/', $sha256) !== 1) {
            throw new SpecException(sprintf('Not a sha256: %s', $sha256));
        }

        return new self([$path => $sha256] + $this->sums);
    }

    public function render(): string
    {
        $sums = $this->sums;
        ksort($sums, SORT_STRING);
        $out = '';
        foreach ($sums as $path => $sha) {
            $out .= $sha . '  ' . $path . "\n";
        }

        return $out;
    }
}
