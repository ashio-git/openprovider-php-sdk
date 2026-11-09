<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * tools/spec-sync
 *
 *   --verify (default)        offline: the vendored inputs match spec/SHA256SUMS
 *   --update [--commit=SHA]   download the pinned (or given) commit and the portal bundle
 *   --check-upstream [--report=FILE]
 *                             exit 1 and write a Markdown report when upstream changed
 *
 * Exit codes: 0 ok, 1 problems or drift, 2 usage or download error.
 */
final class SpecSyncCommand
{
    /**
     * @param list<string> $arguments
     * @param resource     $out
     * @param resource     $err
     */
    public static function run(array $arguments, string $specDir, Fetcher $fetcher, string $today, $out, $err): int
    {
        $options = [];
        foreach ($arguments as $argument) {
            if (preg_match('/^--(verify|update|check-upstream)$/', $argument, $m) === 1) {
                $options[$m[1]] = true;
            } elseif (preg_match('/^--(commit|report)=(.+)$/', $argument, $m) === 1) {
                $options[$m[1]] = $m[2];
            } else {
                fwrite($err, "Unknown option: $argument\nUsage: tools/spec-sync [--verify | --update [--commit=SHA] | --check-upstream [--report=FILE]]\n");

                return 2;
            }
        }

        try {
            $sync = new SpecSync($specDir, $fetcher);
            if (isset($options['update'])) {
                $commit = $options['commit'] ?? null;
                $sync->update(is_string($commit) ? $commit : null, $today);
                fwrite($out, "Downloaded the inputs; run tools/spec-merge and review spec/conflicts.md.\n");

                return 0;
            }
            if (isset($options['check-upstream'])) {
                $drift = $sync->checkUpstream();
                $report = $options['report'] ?? null;
                if ($drift === []) {
                    fwrite($out, "No drift: upstream still serves the pinned inputs.\n");

                    return 0;
                }
                $markdown = self::driftReport($drift, SourceManifest::load($specDir));
                if (is_string($report)) {
                    file_put_contents($report, $markdown);
                }
                fwrite($out, $markdown);

                return 1;
            }

            $problems = $sync->verify();
            if ($problems !== []) {
                fwrite($err, implode("\n", $problems) . "\n");

                return 1;
            }
            fwrite($out, "Every vendored input matches spec/SHA256SUMS.\n");

            return 0;
        } catch (SpecException $e) {
            fwrite($err, $e->getMessage() . "\n");

            return 2;
        }
    }

    /**
     * @param list<string> $drift
     */
    private static function driftReport(array $drift, SourceManifest $manifest): string
    {
        return implode("\n", [
            '## Openprovider spec drift',
            '',
            sprintf('The inputs pinned in `spec/sources.json` (commit `%s`, retrieved %s) no longer match upstream:', $manifest->commit, $manifest->retrieved()),
            '',
            ...array_map(static fn (string $line): string => '- ' . $line, $drift),
            '',
            'To take the change:',
            '',
            sprintf('1. Find the new commit on `%s` (`%s`) and run `php tools/spec-sync --update --commit=<sha>`.', $manifest->branch, $manifest->repository),
            '2. Run `php tools/spec-merge` and review `spec/conflicts.md`; settle new conflicts in `spec/overrides.json`.',
            '3. Update `spec/PROVENANCE.md` and run `composer quality`.',
            '',
        ]);
    }
}
