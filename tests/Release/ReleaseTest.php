<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Release;

use Ashio\OpenProvider\Sdk;
use Ashio\OpenProvider\Tests\Support\ArrayPath;
use Ashio\OpenProvider\Tests\Support\SpecOperations;
use PHPUnit\Framework\TestCase;

/**
 * What a release relies on stays consistent: the version the SDK reports (and sends in its
 * User-Agent) is the one the changelog describes, each version appears once with a link, and the
 * license, composer.json and the README name the same author. The release workflow refuses a tag
 * that differs from Sdk::VERSION.
 */
final class ReleaseTest extends TestCase
{
    public function testTheVersionIsSemanticAndTheChangelogDescribesIt(): void
    {
        self::assertMatchesRegularExpression('/^\d+\.\d+\.\d+(-[0-9A-Za-z.-]+)?$/', Sdk::VERSION);
        $changelog = self::read('CHANGELOG.md');

        self::assertMatchesRegularExpression('/^## \[Unreleased\]$/m', $changelog, 'the changelog keeps an Unreleased section');
        if (!str_contains(Sdk::VERSION, '-')) {
            self::assertMatchesRegularExpression(sprintf('/^## \[%s\] - \d{4}-\d{2}-\d{2}$/m', preg_quote(Sdk::VERSION, '/')), $changelog, 'Sdk::VERSION is released: the changelog dates it');
        }
        self::assertSame('Unreleased', self::versions($changelog)[0] ?? null, 'Unreleased comes first');
    }

    public function testEachVersionAppearsOnceAndHasALink(): void
    {
        $changelog = self::read('CHANGELOG.md');
        $versions = self::versions($changelog);

        self::assertSame(array_values(array_unique($versions)), $versions, 'a version heading appears twice');
        foreach ($versions as $version) {
            self::assertMatchesRegularExpression(sprintf('/^\[%s\]: https:\/\/github\.com\//m', preg_quote($version, '/')), $changelog, "[$version] has no link");
        }
    }

    public function testTheLicenseComposerAndReadmeNameTheSameAuthor(): void
    {
        $composer = json_decode(self::read('composer.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);
        self::assertArrayNotHasKey('version', $composer, 'versions come from Git tags');
        self::assertSame('MIT', $composer['license'] ?? null);
        $author = ArrayPath::at($composer, 'authors', '0', 'name');
        self::assertIsString($author);

        self::assertMatchesRegularExpression('/^Copyright \(c\) \d{4}(-\d{4})? ' . preg_quote($author, '/') . '$/m', self::read('LICENSE'));
        self::assertStringContainsString('MIT © ' . $author, self::read('README.md'));
        self::assertStringContainsString('Copyright (c) ', self::read('NOTICE'));
        self::assertStringContainsString($author, self::read('NOTICE'));
    }

    /**
     * The version headings of the changelog, in order (the archived ones are `###`, not `##`).
     *
     * @return list<string>
     */
    private static function versions(string $changelog): array
    {
        preg_match_all('/^## \[([^\]]+)\]/m', $changelog, $matches);

        return $matches[1];
    }

    private static function read(string $file): string
    {
        $contents = file_get_contents(SpecOperations::ROOT . '/' . $file);
        self::assertIsString($contents, "Cannot read $file");

        return $contents;
    }
}
