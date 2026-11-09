<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Generate;

use Ashio\OpenProvider\Tools\Spec\SpecException;

/**
 * tools/generate errors [--check]
 *
 * Writes the generated sources, or with --check exits 1 when the committed ones differ.
 */
final class GenerateCommand
{
    /**
     * @param list<string> $arguments
     * @param resource     $out
     * @param resource     $err
     */
    public static function run(array $arguments, string $root, $out, $err): int
    {
        $check = in_array('--check', $arguments, true);
        $targets = array_values(array_diff($arguments, ['--check']));
        if ($targets !== ['errors']) {
            fwrite($err, "Usage: tools/generate errors [--check]\n");

            return 2;
        }

        try {
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
            if (file_put_contents($file, $source) !== strlen($source)) {
                throw new SpecException('Cannot write ' . $file);
            }
            fwrite($out, "Wrote src/Exception/ErrorCatalog.php\n");

            return 0;
        } catch (SpecException $e) {
            fwrite($err, $e->getMessage() . "\n");

            return 2;
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
