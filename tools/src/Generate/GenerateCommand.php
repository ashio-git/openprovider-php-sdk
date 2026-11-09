<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Generate;

use Ashio\OpenProvider\Tools\Spec\SpecException;

/**
 * tools/generate <errors|models>... [--check]
 *
 * - errors: src/Exception/ErrorCatalog.php from spec/error-codes.json and spec/behavior.json.
 * - models: src/Model from spec/openprovider.json. src/Model holds generated files only: files
 *   the spec no longer yields are removed, and a hand-written file there is refused.
 *
 * Writes the generated sources, or with --check exits 1 when the committed ones differ. Exits 2
 * on bad usage or an input the generator refuses.
 */
final class GenerateCommand
{
    private const TARGETS = ['errors', 'models'];

    /**
     * @param list<string> $arguments
     * @param resource     $out
     * @param resource     $err
     */
    public static function run(array $arguments, string $root, $out, $err): int
    {
        $check = in_array('--check', $arguments, true);
        $targets = array_values(array_unique(array_diff($arguments, ['--check'])));
        if ($targets === [] || array_diff($targets, self::TARGETS) !== []) {
            fwrite($err, "Usage: tools/generate <errors|models>... [--check]\n");

            return 2;
        }

        try {
            $status = 0;
            foreach ($targets as $target) {
                $status = max($status, $target === 'errors'
                    ? self::errors($root, $check, $out, $err)
                    : self::models($root, $check, $out, $err));
            }

            return $status;
        } catch (SpecException $e) {
            fwrite($err, $e->getMessage() . "\n");

            return 2;
        }
    }

    /**
     * @param resource $out
     * @param resource $err
     */
    private static function errors(string $root, bool $check, $out, $err): int
    {
        $file = $root . '/src/Exception/ErrorCatalog.php';
        $source = ErrorCatalogGenerator::generate(
            self::read($root . '/spec/error-codes.json'),
            self::read($root . '/spec/behavior.json'),
        );
        if ($check) {
            if (@file_get_contents($file) !== $source) {
                fwrite($err, "src/Exception/ErrorCatalog.php is out of date: run tools/generate errors\n");

                return 1;
            }
            fwrite($out, "src/Exception/ErrorCatalog.php is up to date.\n");

            return 0;
        }
        self::write($file, $source);
        fwrite($out, "Wrote src/Exception/ErrorCatalog.php\n");

        return 0;
    }

    /**
     * @param resource $out
     * @param resource $err
     */
    private static function models(string $root, bool $check, $out, $err): int
    {
        $directory = $root . '/src/Model';
        $files = ModelGenerator::generate(self::read($root . '/spec/openprovider.json'));
        $existing = self::existing($directory);
        $changed = array_values(array_filter(
            array_keys($files),
            static fn (string $path): bool => ($existing[$path] ?? null) !== $files[$path],
        ));
        $stale = array_values(array_diff(array_keys($existing), array_keys($files)));
        $manual = array_values(array_filter($stale, static fn (string $path): bool => !str_contains($existing[$path], ModelGenerator::MARKER)));

        if ($check) {
            if ($changed === [] && $stale === []) {
                fwrite($out, sprintf("src/Model is up to date (%d files).\n", count($files)));

                return 0;
            }
            foreach ($changed as $path) {
                fwrite($err, sprintf("src/Model/%s is %s\n", $path, isset($existing[$path]) ? 'out of date' : 'missing'));
            }
            foreach ($stale as $path) {
                fwrite($err, sprintf("src/Model/%s %s\n", $path, in_array($path, $manual, true) ? 'is not generated' : 'is no longer generated'));
            }
            fwrite($err, "Run tools/generate models\n");

            return 1;
        }

        if ($manual !== []) {
            foreach ($manual as $path) {
                fwrite($err, sprintf("src/Model/%s is not generated: src/Model holds generated code only, move it elsewhere\n", $path));
            }

            return 2;
        }
        foreach ($changed as $path) {
            self::write($directory . '/' . $path, $files[$path]);
        }
        foreach ($stale as $path) {
            if (!unlink($directory . '/' . $path)) {
                throw new SpecException('Cannot remove ' . $directory . '/' . $path);
            }
            for ($dir = dirname($directory . '/' . $path); $dir !== $directory && self::isEmpty($dir); $dir = dirname($dir)) {
                rmdir($dir);
            }
        }
        fwrite($out, sprintf("Wrote %d files under src/Model: %d changed, removed %d.\n", count($files), count($changed), count($stale)));

        return 0;
    }

    /**
     * @return array<string, string> path relative to $directory => contents
     */
    private static function existing(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile()) {
                $files[substr($file->getPathname(), strlen($directory) + 1)] = self::read($file->getPathname());
            }
        }
        ksort($files, SORT_STRING);

        return $files;
    }

    private static function isEmpty(string $directory): bool
    {
        return is_dir($directory) && (scandir($directory) ?: []) === ['.', '..'];
    }

    private static function write(string $file, string $contents): void
    {
        if (!is_dir(dirname($file)) && !mkdir(dirname($file), 0o777, true) && !is_dir(dirname($file))) {
            throw new SpecException('Cannot create ' . dirname($file));
        }
        if (file_put_contents($file, $contents) !== strlen($contents)) {
            throw new SpecException('Cannot write ' . $file);
        }
    }

    private static function read(string $file): string
    {
        $contents = @file_get_contents($file);
        if ($contents === false) {
            throw new SpecException('Cannot read ' . $file);
        }

        return $contents;
    }
}
