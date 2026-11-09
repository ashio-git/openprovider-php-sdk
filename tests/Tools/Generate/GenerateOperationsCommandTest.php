<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Generate;

use Ashio\OpenProvider\Tools\Generate\GenerateCommand;
use PHPUnit\Framework\TestCase;

/**
 * `tools/generate operations` owns src/Service and src/OpenProvider.php: it writes what the spec
 * yields, finds them up to date afterwards, and never accepts a hand-written file in src/Service.
 */
final class GenerateOperationsCommandTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/op-generate-operations-' . bin2hex(random_bytes(6));
        mkdir($this->root . '/spec', 0o777, true);
        file_put_contents($this->root . '/spec/openprovider.json', json_encode(OperationGeneratorTest::spec(), JSON_THROW_ON_ERROR));
        file_put_contents($this->root . '/spec/behavior.json', json_encode(['authentication' => ['login' => ['operation' => 'POST /auth/login']]], JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($files as $file) {
            self::assertInstanceOf(\SplFileInfo::class, $file);
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->root);
    }

    public function testItWritesTheServicesAndTheFacadeThenFindsThemUpToDate(): void
    {
        [$code, $out] = $this->generate(['operations']);
        self::assertSame(0, $code);
        self::assertStringContainsString('Wrote 4 files under src/Service', $out);
        self::assertFileExists($this->root . '/src/Service/DnsService.php');
        self::assertFileExists($this->root . '/src/OpenProvider.php');

        [$code, $out] = $this->generate(['operations', '--check']);
        self::assertSame(0, $code, $out);
        self::assertStringContainsString('src/Service is up to date (4 files)', $out);
        self::assertStringContainsString('src/OpenProvider.php is up to date', $out);
    }

    public function testCheckNamesAnEditedFacade(): void
    {
        $this->generate(['operations']);
        file_put_contents($this->root . '/src/OpenProvider.php', "// edited by hand\n", FILE_APPEND);

        [$code, , $err] = $this->generate(['operations', '--check']);
        self::assertSame(1, $code);
        self::assertStringContainsString('src/OpenProvider.php is out of date', $err);

        self::assertSame(0, $this->generate(['operations'])[0]);
        self::assertSame(0, $this->generate(['operations', '--check'])[0]);
    }

    public function testAHandWrittenServiceIsRefusedAndKept(): void
    {
        $this->generate(['operations']);
        $manual = $this->root . '/src/Service/HandWritten.php';
        file_put_contents($manual, "<?php\n");

        [$code, , $err] = $this->generate(['operations', '--check']);
        self::assertSame(1, $code);
        self::assertStringContainsString('src/Service/HandWritten.php is not generated', $err);

        [$code, , $err] = $this->generate(['operations']);
        self::assertSame(2, $code);
        self::assertStringContainsString('src/Service holds generated code only', $err);
        self::assertFileExists($manual);
    }

    /**
     * @param list<string> $arguments
     *
     * @return array{int, string, string}
     */
    private function generate(array $arguments): array
    {
        $out = fopen('php://memory', 'w+');
        $err = fopen('php://memory', 'w+');
        self::assertIsResource($out);
        self::assertIsResource($err);
        $code = GenerateCommand::run($arguments, $this->root, $out, $err);
        rewind($out);
        rewind($err);

        return [$code, (string) stream_get_contents($out), (string) stream_get_contents($err)];
    }
}
