<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Spec;

use Ashio\OpenProvider\Tools\Spec\Json;
use Ashio\OpenProvider\Tools\Spec\Sha256Sums;
use Ashio\OpenProvider\Tools\Spec\SourceManifest;
use PHPUnit\Framework\TestCase;

/**
 * spec/PROVENANCE.md, error-codes.json and behavior.json stay consistent with the data they describe.
 */
final class ProvenanceTest extends TestCase
{
    private const SPEC_DIR = __DIR__ . '/../../spec';

    public function testProvenanceNamesThePinnedCommitAndEveryInputHash(): void
    {
        $provenance = (string) file_get_contents(self::SPEC_DIR . '/PROVENANCE.md');
        $manifest = SourceManifest::load(self::SPEC_DIR);
        $sums = Sha256Sums::parse((string) file_get_contents(self::SPEC_DIR . '/SHA256SUMS'));

        self::assertStringContainsString($manifest->commit, $provenance);
        foreach (array_keys($manifest->inputPaths()) as $path) {
            $sha = $sums->get($path);
            self::assertNotNull($sha, $path);
            self::assertStringContainsString($sha, $provenance, $path);
        }
    }

    public function testTheErrorCatalogHasTheKnowledgeBaseCodesOnceEach(): void
    {
        $catalog = Json::object(Json::decode((string) file_get_contents(self::SPEC_DIR . '/error-codes.json')), 'catalog');
        $codes = Json::object($catalog['codes'], 'codes');

        self::assertCount(608, $codes);
        foreach ([0, 196, 10005, 10006, 4007] as $code) {
            self::assertArrayHasKey($code, $codes);
        }
        self::assertSame('Authentication/Authorization Failed', Json::object($codes[196], '196')['description']);
    }

    public function testEveryClassifiedErrorCodeExistsAndBelongsToOneClass(): void
    {
        $behavior = Json::object(Json::decode((string) file_get_contents(self::SPEC_DIR . '/behavior.json')), 'behavior');
        $codes = Json::object(Json::object(Json::decode((string) file_get_contents(self::SPEC_DIR . '/error-codes.json')), 'catalog')['codes'], 'codes');
        $seen = [];
        foreach (Json::object($behavior['errorClasses'], 'errorClasses') as $class => $definition) {
            if ($class === '$comment') {
                continue;
            }
            foreach (Json::list(Json::object($definition, (string) $class)['codes'], 'codes') as $code) {
                self::assertIsInt($code);
                self::assertArrayHasKey($code, $codes, "$class lists unknown code $code");
                self::assertArrayNotHasKey($code, $seen, "$code is in $class and " . ($seen[$code] ?? ''));
                $seen[$code] = (string) $class;
            }
        }

        self::assertSame('authentication', $seen[196]);
        self::assertSame('rateLimited', $seen[4007]);
        self::assertSame('maintenance', $seen[4005]);
        self::assertSame('notFound', $seen[872]);
        self::assertSame('validation', $seen[80]);
    }
}
