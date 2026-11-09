<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * tools/spec-merge
 *
 *   (no option)  merge spec/ inputs; write spec/openprovider.json, spec/conflicts.md and the
 *                openprovider.json line of spec/SHA256SUMS. Exit 1 on unresolved conflicts.
 *   --check      merge in memory and exit 1 unless the committed files are byte-identical.
 */
final class SpecMergeCommand
{
    public const REPORT = 'conflicts.md';

    /**
     * @param list<string> $arguments
     * @param resource     $out
     * @param resource     $err
     */
    public static function run(array $arguments, string $specDir, $out, $err): int
    {
        try {
            $check = in_array('--check', $arguments, true);
            $unknown = array_diff($arguments, ['--check']);
            if ($unknown !== []) {
                fwrite($err, 'Unknown option(s): ' . implode(' ', $unknown) . "\nUsage: tools/spec-merge [--check]\n");

                return 2;
            }

            $inputs = SpecInputs::load($specDir);
            $result = $inputs->merge();
            $unresolved = $result->unresolvedConflicts() !== [];
            $merged = $unresolved ? '' : Json::encode($result->spec());
            $sha = hash('sha256', $merged);
            $report = MergeReport::render($result, $inputs->manifest, $sha);
            $sumsFile = $specDir . '/' . SpecSync::SUMS;
            $sums = Sha256Sums::parse(is_file($sumsFile) ? (string) file_get_contents($sumsFile) : '');

            if ($check) {
                return self::check($specDir, $merged, $report, $sums, $unresolved, $result, $err, $out);
            }

            self::write($specDir . '/' . self::REPORT, $report);
            if ($unresolved) {
                fwrite($err, $report);
                fwrite($err, "Unresolved conflicts: spec/openprovider.json was not written.\n");

                return 1;
            }
            self::write($specDir . '/' . SpecSync::MERGED, $merged);
            self::write($sumsFile, $sums->with(SpecSync::MERGED, $sha)->render());
            fwrite($out, sprintf(
                "Merged %d operations and %d definitions; openprovider.json sha256 %s\n",
                $result->operationCount(),
                $result->definitionCount(),
                $sha,
            ));

            return 0;
        } catch (SpecException $e) {
            fwrite($err, $e->getMessage() . "\n");

            return 2;
        }
    }

    /**
     * @param resource $err
     * @param resource $out
     */
    private static function check(
        string $specDir,
        string $merged,
        string $report,
        Sha256Sums $sums,
        bool $unresolved,
        MergeResult $result,
        $err,
        $out,
    ): int {
        $problems = [];
        if ($unresolved) {
            $problems[] = sprintf('%d unresolved conflict(s); see spec/conflicts.md after running tools/spec-merge', count($result->unresolvedConflicts()));
        } else {
            if (@file_get_contents($specDir . '/' . SpecSync::MERGED) !== $merged) {
                $problems[] = 'spec/openprovider.json differs from a fresh merge (edited by hand, or inputs changed without a merge)';
            }
            if ($sums->get(SpecSync::MERGED) !== hash('sha256', $merged)) {
                $problems[] = 'spec/SHA256SUMS does not hold the sha256 of a fresh merge';
            }
        }
        if (@file_get_contents($specDir . '/' . self::REPORT) !== $report) {
            $problems[] = 'spec/conflicts.md differs from a fresh merge';
        }
        if ($problems !== []) {
            fwrite($err, implode("\n", $problems) . "\nRun tools/spec-merge and commit the result.\n");

            return 1;
        }
        fwrite($out, sprintf("spec/openprovider.json is reproducible (%d operations).\n", $result->operationCount()));

        return 0;
    }

    private static function write(string $file, string $contents): void
    {
        if (file_put_contents($file, $contents) !== strlen($contents)) {
            throw new SpecException('Cannot write ' . $file);
        }
    }
}
