<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Spec;

use Ashio\OpenProvider\Tools\Spec\Fetched;
use Ashio\OpenProvider\Tools\Spec\Json;
use Ashio\OpenProvider\Tools\Spec\Sha256Sums;
use Ashio\OpenProvider\Tools\Spec\SpecSync;
use PHPUnit\Framework\TestCase;

final class SpecSyncTest extends TestCase
{
    private const COMMIT = '1111111111111111111111111111111111111111';
    private const RAW = 'https://raw.githubusercontent.com/openprovider/api-documentation/';
    private const PORTAL = 'https://developer.openprovider.com/data/swagger.spec.js';
    private const LISTING = 'https://api.github.com/repos/openprovider/api-documentation/contents/swagger?ref=v1beta';

    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/spec-sync-' . bin2hex(random_bytes(6));
        mkdir($this->dir);
        file_put_contents($this->dir . '/sources.json', Json::encode([
            'retrieved' => '2026-01-01',
            'services' => [
                'repository' => 'openprovider/api-documentation',
                'branch' => 'v1beta',
                'commit' => self::COMMIT,
                'directory' => 'swagger',
                'files' => ['auth', 'dns'],
            ],
            'portal' => ['url' => self::PORTAL, 'variable' => 'window.OP_API_SPEC'],
        ]));
    }

    protected function tearDown(): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($files as $file) {
            self::assertInstanceOf(\SplFileInfo::class, $file);
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->dir);
    }

    public function testUpdateDownloadsThePinnedFilesAndRecordsTheirHashes(): void
    {
        $fetcher = $this->upstream();

        (new SpecSync($this->dir, $fetcher))->update(null, '2026-11-09');

        self::assertSame('{"auth":1}', file_get_contents($this->dir . '/upstream/api-documentation/swagger/auth.swagger.json'));
        self::assertSame(self::bundle(), file_get_contents($this->dir . '/upstream/developer-portal/swagger.spec.js'));
        $sums = Sha256Sums::parse((string) file_get_contents($this->dir . '/SHA256SUMS'));
        self::assertSame(hash('sha256', '{"dns":1}'), $sums->get('upstream/api-documentation/swagger/dns.swagger.json'));
        self::assertSame(hash('sha256', self::bundle()), $sums->get('upstream/developer-portal/swagger.spec.js'));
        $manifest = Json::object(Json::decode((string) file_get_contents($this->dir . '/sources.json')), 'manifest');
        self::assertSame('2026-11-09', $manifest['retrieved']);
        self::assertSame(['url' => self::PORTAL, 'variable' => 'window.OP_API_SPEC', 'etag' => '"abc"', 'lastModified' => 'Mon, 19 Oct 2026 20:49:58 GMT'], $manifest['portal']);
        self::assertContains(self::RAW . self::COMMIT . '/swagger/auth.swagger.json', $fetcher->requested);
    }

    public function testUpdateCanMoveThePinToAnotherCommit(): void
    {
        $other = '2222222222222222222222222222222222222222';
        $fetcher = $this->upstream();
        $fetcher->responses[self::RAW . $other . '/swagger/auth.swagger.json'] = '{"auth":2}';
        $fetcher->responses[self::RAW . $other . '/swagger/dns.swagger.json'] = '{"dns":2}';

        (new SpecSync($this->dir, $fetcher))->update($other, '2026-11-09');

        $manifest = Json::object(Json::decode((string) file_get_contents($this->dir . '/sources.json')), 'manifest');
        self::assertSame($other, Json::object($manifest['services'], 'services')['commit']);
        self::assertSame('{"auth":2}', file_get_contents($this->dir . '/upstream/api-documentation/swagger/auth.swagger.json'));
    }

    public function testUpdateRefusesAPortalBundleItCannotRead(): void
    {
        $fetcher = $this->upstream();
        $fetcher->responses[self::PORTAL] = 'alert(1)';

        try {
            (new SpecSync($this->dir, $fetcher))->update(null, '2026-11-09');
            self::fail('expected the update to refuse the bundle');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('window.OP_API_SPEC', $e->getMessage());
        }
        self::assertFileDoesNotExist($this->dir . '/upstream/developer-portal/swagger.spec.js');
    }

    public function testVerifyPassesForUntouchedFilesAndNamesTheTamperedOne(): void
    {
        $sync = new SpecSync($this->dir, $this->upstream());
        $sync->update(null, '2026-11-09');

        self::assertSame([], $sync->verify());

        file_put_contents($this->dir . '/upstream/api-documentation/swagger/dns.swagger.json', '{"dns":"edited"}');
        unlink($this->dir . '/upstream/api-documentation/swagger/auth.swagger.json');

        self::assertSame([
            'upstream/api-documentation/swagger/auth.swagger.json is missing',
            'upstream/api-documentation/swagger/dns.swagger.json does not match SHA256SUMS',
        ], $sync->verify());
    }

    public function testVerifyFlagsAFileTheManifestDoesNotKnow(): void
    {
        $sync = new SpecSync($this->dir, $this->upstream());
        $sync->update(null, '2026-11-09');
        file_put_contents(
            $this->dir . '/SHA256SUMS',
            (string) file_get_contents($this->dir . '/SHA256SUMS') . str_repeat('0', 64) . "  upstream/stray.json\n",
        );

        self::assertSame(['SHA256SUMS lists upstream/stray.json, which no source declares'], $sync->verify());
    }

    public function testNoDriftWhenTheBranchStillServesThePinnedBytes(): void
    {
        $sync = new SpecSync($this->dir, $this->upstream());
        $sync->update(null, '2026-11-09');

        self::assertSame([], $sync->checkUpstream());
    }

    public function testDriftNamesChangedAddedAndRemovedFilesAndThePortal(): void
    {
        $fetcher = $this->upstream();
        $sync = new SpecSync($this->dir, $fetcher);
        $sync->update(null, '2026-11-09');

        $fetcher->responses[self::RAW . 'v1beta/swagger/dns.swagger.json'] = '{"dns":"new"}';
        $fetcher->responses[self::LISTING] = Json::encode([
            ['name' => 'auth.swagger.json', 'type' => 'file'],
            ['name' => 'dns.swagger.json', 'type' => 'file'],
            ['name' => 'webhook.swagger.json', 'type' => 'file'],
        ]);
        $fetcher->responses[self::PORTAL] = new Fetched('window.OP_API_SPEC = {"v":2};', '"def"');

        self::assertSame([
            'swagger/dns.swagger.json changed on v1beta since ' . self::COMMIT,
            'swagger/webhook.swagger.json was added on v1beta',
            'the portal bundle changed (ETag "abc" → "def")',
        ], $sync->checkUpstream());

        $fetcher->responses[self::LISTING] = Json::encode([['name' => 'auth.swagger.json', 'type' => 'file']]);
        self::assertContains('swagger/dns.swagger.json was removed on v1beta', $sync->checkUpstream());
    }

    private function upstream(): FakeFetcher
    {
        return new FakeFetcher([
            self::RAW . self::COMMIT . '/swagger/auth.swagger.json' => '{"auth":1}',
            self::RAW . self::COMMIT . '/swagger/dns.swagger.json' => '{"dns":1}',
            self::RAW . 'v1beta/swagger/auth.swagger.json' => '{"auth":1}',
            self::RAW . 'v1beta/swagger/dns.swagger.json' => '{"dns":1}',
            self::LISTING => Json::encode([
                ['name' => 'auth.swagger.json', 'type' => 'file'],
                ['name' => 'dns.swagger.json', 'type' => 'file'],
            ]),
            self::PORTAL => new Fetched(self::bundle(), '"abc"', 'Mon, 19 Oct 2026 20:49:58 GMT'),
        ]);
    }

    private static function bundle(): string
    {
        return 'window.OP_API_SPEC = {"v":1};';
    }
}
