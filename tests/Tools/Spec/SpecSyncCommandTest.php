<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Spec;

use Ashio\OpenProvider\Tools\Spec\Fetched;
use Ashio\OpenProvider\Tools\Spec\Json;
use Ashio\OpenProvider\Tools\Spec\SpecSyncCommand;
use PHPUnit\Framework\TestCase;

final class SpecSyncCommandTest extends TestCase
{
    private const COMMIT = '1111111111111111111111111111111111111111';
    private const RAW = 'https://raw.githubusercontent.com/o/r/';
    private const PORTAL = 'https://portal.test/swagger.spec.js';
    private const LISTING = 'https://api.github.com/repos/o/r/contents/swagger?ref=main';

    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/spec-sync-cmd-' . bin2hex(random_bytes(6));
        mkdir($this->dir);
        file_put_contents($this->dir . '/sources.json', Json::encode([
            'retrieved' => '2026-01-01',
            'services' => ['repository' => 'o/r', 'branch' => 'main', 'commit' => self::COMMIT, 'directory' => 'swagger', 'files' => ['auth']],
            'portal' => ['url' => self::PORTAL, 'variable' => 'window.OP_API_SPEC'],
        ]));
    }

    protected function tearDown(): void
    {
        foreach (['/upstream/api-documentation/swagger/auth.swagger.json', '/upstream/developer-portal/swagger.spec.js', '/SHA256SUMS', '/sources.json', '/drift.md'] as $file) {
            @unlink($this->dir . $file);
        }
        foreach (['/upstream/api-documentation/swagger', '/upstream/api-documentation', '/upstream/developer-portal', '/upstream', ''] as $dir) {
            @rmdir($this->dir . $dir);
        }
    }

    public function testUpdateThenVerifyThenNoDrift(): void
    {
        $fetcher = $this->fetcher();

        self::assertSame(0, $this->invoke(['--update'], $fetcher)[0]);
        self::assertSame(0, $this->invoke([], $fetcher)[0]);
        self::assertSame(0, $this->invoke(['--check-upstream'], $fetcher)[0]);
    }

    public function testVerifyExitsOneOnATamperedInput(): void
    {
        $fetcher = $this->fetcher();
        $this->invoke(['--update'], $fetcher);
        file_put_contents($this->dir . '/upstream/api-documentation/swagger/auth.swagger.json', '{}');

        [$exit, , $err] = $this->invoke(['--verify'], $fetcher);

        self::assertSame(1, $exit);
        self::assertStringContainsString('auth.swagger.json does not match', $err);
    }

    public function testDriftExitsOneAndWritesTheReport(): void
    {
        $fetcher = $this->fetcher();
        $this->invoke(['--update'], $fetcher);
        $fetcher->responses[self::RAW . 'main/swagger/auth.swagger.json'] = '{"auth":2}';

        [$exit, $out] = $this->invoke(['--check-upstream', '--report=' . $this->dir . '/drift.md'], $fetcher);

        self::assertSame(1, $exit);
        self::assertStringContainsString('swagger/auth.swagger.json changed on main', $out);
        self::assertSame($out, file_get_contents($this->dir . '/drift.md'));
    }

    public function testADownloadErrorExitsTwo(): void
    {
        [$exit, , $err] = $this->invoke(['--update'], new FakeFetcher([]));

        self::assertSame(2, $exit);
        self::assertStringContainsString('404', $err);
    }

    public function testAnUnknownOptionExitsTwo(): void
    {
        self::assertSame(2, $this->invoke(['--force'], $this->fetcher())[0]);
    }

    private function fetcher(): FakeFetcher
    {
        return new FakeFetcher([
            self::RAW . self::COMMIT . '/swagger/auth.swagger.json' => '{"auth":1}',
            self::RAW . 'main/swagger/auth.swagger.json' => '{"auth":1}',
            self::LISTING => '[{"name":"auth.swagger.json"}]',
            self::PORTAL => new Fetched('window.OP_API_SPEC = {"v":1};', '"e"'),
        ]);
    }

    /**
     * @param list<string> $arguments
     *
     * @return array{int, string, string}
     */
    private function invoke(array $arguments, FakeFetcher $fetcher): array
    {
        $out = fopen('php://memory', 'w+');
        $err = fopen('php://memory', 'w+');
        self::assertIsResource($out);
        self::assertIsResource($err);

        $exit = SpecSyncCommand::run($arguments, $this->dir, $fetcher, '2026-11-09', $out, $err);
        rewind($out);
        rewind($err);

        return [$exit, (string) stream_get_contents($out), (string) stream_get_contents($err)];
    }
}
