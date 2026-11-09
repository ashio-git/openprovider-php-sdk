<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Generate;

use Ashio\OpenProvider\Tools\Spec\SpecException;

/**
 * tools/generate <errors|models|operations|readme>... [--check]
 *
 * - errors: src/Exception/ErrorCatalog.php from spec/error-codes.json and spec/behavior.json.
 * - models: src/Model from spec/openprovider.json. src/Model holds generated files only: files
 *   the spec no longer yields are removed, and a hand-written file there is refused.
 * - operations: src/Service (same rules as src/Model) and src/OpenProvider.php, from
 *   spec/openprovider.json and spec/behavior.json.
 * - readme: the generated regions of README.md (badges, numbers, the operation reference and the
 *   error table), from the generated code, the spec pin and the gates' configuration.
 *
 * Writes the generated sources, or with --check exits 1 when the committed ones differ. Exits 2
 * on bad usage or an input the generator refuses.
 */
final class GenerateCommand
{
    private const TARGETS = ['errors', 'models', 'operations', 'readme'];

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
            fwrite($err, "Usage: tools/generate <errors|models|operations|readme>... [--check]\n");

            return 2;
        }

        try {
            $status = 0;
            foreach ($targets as $target) {
                $status = max($status, match ($target) {
                    'errors' => self::errors($root, $check, $out, $err),
                    'models' => self::models($root, $check, $out, $err),
                    'readme' => self::readme($root, $check, $out, $err),
                    default => self::operations($root, $check, $out, $err),
                });
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
    private static function readme(string $root, bool $check, $out, $err): int
    {
        $file = $root . '/README.md';
        $current = self::read($file);
        $readme = ReadmeGenerator::render($current, ReadmeInventory::fromCode($root));
        if ($check) {
            if ($current !== $readme) {
                fwrite($err, "README.md is out of date: run tools/generate readme\n");

                return 1;
            }
            fwrite($out, "README.md is up to date.\n");

            return 0;
        }
        self::write($file, $readme);
        fwrite($out, "Wrote the generated regions of README.md\n");

        return 0;
    }

    /**
     * @param resource $out
     * @param resource $err
     */
    private static function models(string $root, bool $check, $out, $err): int
    {
        $files = ModelGenerator::generate(self::read($root . '/spec/openprovider.json'));

        return self::directory($root, 'src/Model', $files, ModelGenerator::MARKER, 'models', $check, $out, $err);
    }

    /**
     * @param resource $out
     * @param resource $err
     */
    private static function operations(string $root, bool $check, $out, $err): int
    {
        $files = OperationGenerator::generate(self::read($root . '/spec/openprovider.json'), self::read($root . '/spec/behavior.json'));
        $services = [];
        foreach ($files as $path => $source) {
            if (str_starts_with($path, 'Service/')) {
                $services[substr($path, strlen('Service/'))] = $source;
            }
        }
        $facade = $files['OpenProvider.php'] ?? throw new SpecException('The operation generator wrote no facade');
        $status = self::directory($root, 'src/Service', $services, OperationGenerator::MARKER, 'operations', $check, $out, $err);
        $file = $root . '/src/OpenProvider.php';
        if ($check) {
            if (@file_get_contents($file) !== $facade) {
                fwrite($err, "src/OpenProvider.php is out of date: run tools/generate operations\n");

                return 1;
            }
            fwrite($out, "src/OpenProvider.php is up to date.\n");

            return $status;
        }
        if ($status === 0) {
            self::write($file, $facade);
            fwrite($out, "Wrote src/OpenProvider.php\n");
        }

        return $status;
    }

    /**
     * Makes $relative hold exactly $files: with $check, reports the differences instead. A file
     * there without $marker was written by hand and is refused, never removed.
     *
     * @param array<string, string> $files path relative to $relative => contents
     * @param resource              $out
     * @param resource              $err
     */
    private static function directory(string $root, string $relative, array $files, string $marker, string $target, bool $check, $out, $err): int
    {
        $directory = $root . '/' . $relative;
        $existing = self::existing($directory);
        $changed = array_values(array_filter(
            array_keys($files),
            static fn (string $path): bool => ($existing[$path] ?? null) !== $files[$path],
        ));
        $stale = array_values(array_diff(array_keys($existing), array_keys($files)));
        $manual = array_values(array_filter($stale, static fn (string $path): bool => !str_contains($existing[$path], $marker)));

        if ($check) {
            if ($changed === [] && $stale === []) {
                fwrite($out, sprintf("%s is up to date (%d files).\n", $relative, count($files)));

                return 0;
            }
            foreach ($changed as $path) {
                fwrite($err, sprintf("%s/%s is %s\n", $relative, $path, isset($existing[$path]) ? 'out of date' : 'missing'));
            }
            foreach ($stale as $path) {
                fwrite($err, sprintf("%s/%s %s\n", $relative, $path, in_array($path, $manual, true) ? 'is not generated' : 'is no longer generated'));
            }
            fwrite($err, "Run tools/generate $target\n");

            return 1;
        }

        if ($manual !== []) {
            foreach ($manual as $path) {
                fwrite($err, sprintf("%s/%s is not generated: %s holds generated code only, move it elsewhere\n", $relative, $path, $relative));
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
        fwrite($out, sprintf("Wrote %d files under %s: %d changed, removed %d.\n", count($files), $relative, count($changed), count($stale)));

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
        foreach (self::files($directory) as $file) {
            $files[$file] = self::read($directory . '/' . $file);
        }
        ksort($files, SORT_STRING);

        return $files;
    }

    /**
     * The files under $directory at any depth, relative to it. Symlinked directories are not
     * followed.
     *
     * @return list<string>
     */
    private static function files(string $directory): array
    {
        $entries = scandir($directory);
        $files = [];
        foreach ($entries === false ? [] : $entries as $entry) {
            $path = $directory . '/' . $entry;
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (is_dir($path) && !is_link($path)) {
                foreach (self::files($path) as $file) {
                    $files[] = $entry . '/' . $file;
                }
            } elseif (is_file($path)) {
                $files[] = $entry;
            }
        }

        return $files;
    }

    private static function isEmpty(string $directory): bool
    {
        return is_dir($directory) && scandir($directory) === ['.', '..'];
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
