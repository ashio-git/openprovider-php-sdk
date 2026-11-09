<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Coverage;

use Ashio\OpenProvider\Tools\Coverage\CoverageCommand;
use Ashio\OpenProvider\Tools\Coverage\LineCoverage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * S4, line half: `tools/coverage coverage.xml` reads PHPUnit's Clover report, prints the line
 * coverage of each top-level directory and of the whole, and fails below the minimum. A directory
 * the driver never instrumented (pcov without pcov.directory) reads as 0% and drags the whole
 * down, so the gate catches it instead of reporting a flattering number.
 */
final class CoverageCommandTest extends TestCase
{
    private const ROOT = '/repo';

    private string $file;

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/op-clover-' . bin2hex(random_bytes(6)) . '.xml';
    }

    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    public function testItSumsStatementsPerTopLevelDirectoryAndOverall(): void
    {
        $coverage = LineCoverage::fromClover(self::clover([
            'src/A.php' => [10, 10],
            'src/Deep/B.php' => [30, 27],
            'tools/src/C.php' => [60, 51],
            'src/Empty.php' => [0, 0],
        ]), self::ROOT);

        self::assertSame(['src' => [40, 37], 'tools' => [60, 51]], $coverage->byArea);
        self::assertSame(100, $coverage->statements);
        self::assertSame(88, $coverage->covered);
        self::assertSame(88.0, $coverage->percent());
    }

    /**
     * @return iterable<string, array{int, int, float, bool}>
     */
    public static function boundaries(): iterable
    {
        yield 'exactly the minimum' => [190, 200, 95.0, true];
        yield 'one line short' => [189, 200, 95.0, false];
        yield 'fractional minimum met' => [949, 1000, 94.9, true];
        yield 'fractional minimum missed' => [949, 1000, 94.91, false];
        yield 'nothing covered, minimum 0' => [0, 10, 0.0, true];
        yield 'all covered, minimum 100' => [10, 10, 100.0, true];
    }

    #[DataProvider('boundaries')]
    public function testTheMinimumIsComparedExactly(int $covered, int $statements, float $minimum, bool $meets): void
    {
        $coverage = LineCoverage::fromClover(self::clover(['src/A.php' => [$statements, $covered]]), self::ROOT);

        self::assertSame($meets, $coverage->meets($minimum));
    }

    public function testFilesOutsideTheRootAreTheirOwnArea(): void
    {
        $coverage = LineCoverage::fromClover(self::clover(['src/A.php' => [4, 4]], ['/elsewhere/X.php' => [4, 0]]), self::ROOT);

        self::assertSame(['src' => [4, 4], '/elsewhere/X.php' => [4, 0]], $coverage->byArea);
        self::assertSame(50.0, $coverage->percent());
    }

    public function testItPassesAtTheMinimumAndPrintsEachArea(): void
    {
        $this->write(['src/A.php' => [100, 99], 'tools/src/B.php' => [100, 91]]);

        [$code, $out, $err] = $this->command([$this->file, '--min=95']);

        self::assertSame(0, $code, $err);
        self::assertSame(
            "src      99.00% (99/100)\ntools    91.00% (91/100)\ntotal    95.00% (190/200), minimum 95.00%\n",
            $out,
        );
    }

    public function testItFailsBelowTheMinimum(): void
    {
        $this->write(['src/A.php' => [1000, 949]]);

        [$code, , $err] = $this->command([$this->file]);

        self::assertSame(1, $code);
        self::assertStringContainsString('Line coverage 94.90% is below the minimum of 95.00%', $err);
    }

    public function testAnUninstrumentedDirectoryFailsTheGate(): void
    {
        // What CI measured before: pcov's default directory is src/, so tools/src read as 0.
        $this->write(['src/A.php' => [7851, 7819], 'tools/src/B.php' => [2301, 0]]);

        self::assertSame(1, $this->command([$this->file])[0]);
    }

    /**
     * @return iterable<string, array{list<string>, string}>
     */
    public static function badUsage(): iterable
    {
        yield 'no file' => [[], 'Usage: tools/coverage <clover.xml> [--min=<percent>]'];
        yield 'two files' => [['a.xml', 'b.xml'], 'Usage:'];
        yield 'unknown option' => [['a.xml', '--max=3'], 'Usage:'];
        yield 'minimum not a number' => [['a.xml', '--min=high'], '--min must be a number from 0 to 100'];
        yield 'minimum above 100' => [['a.xml', '--min=101'], '--min must be a number from 0 to 100'];
        yield 'negative minimum' => [['a.xml', '--min=-1'], '--min must be a number from 0 to 100'];
    }

    /**
     * @param list<string> $arguments
     */
    #[DataProvider('badUsage')]
    public function testBadUsageExitsTwo(array $arguments, string $message): void
    {
        [$code, , $err] = $this->command($arguments);

        self::assertSame(2, $code);
        self::assertStringContainsString($message, $err);
    }

    public function testAnUnreadableReportExitsTwo(): void
    {
        [$code, , $err] = $this->command([$this->file]);

        self::assertSame(2, $code);
        self::assertStringContainsString('Cannot read ' . $this->file, $err);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidReports(): iterable
    {
        yield 'not XML' => ['{"lines": 1}', 'is not a Clover report'];
        yield 'no file element' => ['<coverage><project/></coverage>', 'has no executable line'];
        yield 'only empty files' => [self::clover(['src/Empty.php' => [0, 0]]), 'has no executable line'];
        yield 'file without metrics' => ['<coverage><project><file name="/repo/src/A.php"/></project></coverage>', 'has no metrics'];
        yield 'covered above total' => [self::clover(['src/A.php' => [1, 2]]), 'covers 2 of 1 statements'];
        yield 'negative count' => [self::clover(['src/A.php' => [-1, 0]]), 'covers 0 of -1 statements'];
        yield 'count not a number' => ['<coverage><project><file name="/repo/src/A.php"><metrics statements="x" coveredstatements="0"/></file></project></coverage>', 'statements="x" is not a count'];
    }

    #[DataProvider('invalidReports')]
    public function testAnInvalidReportExitsTwo(string $xml, string $message): void
    {
        file_put_contents($this->file, $xml);

        [$code, , $err] = $this->command([$this->file]);

        self::assertSame(2, $code);
        self::assertStringContainsString($message, $err);
    }

    public function testParsingAnInvalidReportThrows(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        LineCoverage::fromClover('<coverage/>', self::ROOT);
    }

    /**
     * @param array<string, array{int, int}> $files
     */
    private function write(array $files): void
    {
        file_put_contents($this->file, self::clover($files));
    }

    /**
     * @param list<string> $arguments
     *
     * @return array{int, string, string}
     */
    private function command(array $arguments): array
    {
        $out = fopen('php://memory', 'w+');
        $err = fopen('php://memory', 'w+');
        self::assertIsResource($out);
        self::assertIsResource($err);

        $code = CoverageCommand::run($arguments, self::ROOT, $out, $err);
        rewind($out);
        rewind($err);

        return [$code, (string) stream_get_contents($out), (string) stream_get_contents($err)];
    }

    /**
     * A Clover report as PHPUnit writes it: files grouped in packages, each with its class
     * metrics and then its own.
     *
     * @param array<string, array{int, int}> $files    path relative to ROOT => [statements, covered]
     * @param array<string, array{int, int}> $absolute absolute path => [statements, covered]
     */
    private static function clover(array $files, array $absolute = []): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><coverage generated="1"><project timestamp="1"><package name="P">';
        $paths = [];
        foreach ($files as $path => $counts) {
            $paths[self::ROOT . '/' . $path] = $counts;
        }
        foreach ([...$paths, ...$absolute] as $path => [$statements, $covered]) {
            $xml .= sprintf(
                '<file name="%s"><class name="C"><metrics statements="999" coveredstatements="0"/></class><metrics statements="%d" coveredstatements="%d"/></file>',
                $path,
                $statements,
                $covered,
            );
        }

        return $xml . '</package><metrics statements="999" coveredstatements="999"/></project></coverage>';
    }
}
