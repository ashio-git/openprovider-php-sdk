<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Generate;

use Ashio\OpenProvider\Exception\ErrorCategory;
use Ashio\OpenProvider\Tests\Support\SpecOperations;
use Ashio\OpenProvider\Tools\Generate\ReadmeGenerator;
use Ashio\OpenProvider\Tools\Generate\ReadmeInventory;
use Ashio\OpenProvider\Tools\Spec\SpecException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * S8: `tools/generate readme` fills the generated regions of README.md from the code and the
 * gates' configuration, and leaves the prose around them alone.
 */
final class ReadmeGeneratorTest extends TestCase
{
    public function testItFillsEveryRegionAndKeepsTheProse(): void
    {
        $readme = self::render("# Title\n\nIntro.\n" . self::regions() . "\nOutro.\n");

        self::assertStringStartsWith("# Title\n\nIntro.\n", $readme);
        self::assertStringEndsWith("\nOutro.\n", $readme);
        self::assertStringContainsString("<!-- generated:badges -->\n[![CI](https://github.com/ashio-git/openprovider-php-sdk/actions/workflows/ci.yml/badge.svg)]", $readme);
        self::assertStringContainsString('![PHP: 8.2 – 8.5](https://img.shields.io/badge/PHP-8.2%20%E2%80%93%208.5-777BB4?logo=php&logoColor=white)', $readme);
        self::assertStringContainsString('![API operations: 3/3](https://img.shields.io/badge/API%20operations-3%2F3-2EA44F)', $readme);
        self::assertStringContainsString('![line coverage: ≥ 95% gate](https://img.shields.io/badge/line%20coverage-%E2%89%A5%2095%25%20gate-2EA44F)', $readme);
        self::assertStringContainsString('![mutation score: ≥ 85.5% gate]', $readme);
        self::assertStringContainsString('![Psalm: level 1]', $readme);
        self::assertStringContainsString('![PHPStan: level max]', $readme);
        self::assertStringContainsString('| **Operations** | 3 of the 3 in the current API reference, in 2 services |', $readme);
        self::assertStringContainsString('| **Models** | 10 immutable, typed models and 2 sets of enum values |', $readme);
        self::assertStringContainsString('| **Error codes** | 12, each mapped to an exception class |', $readme);
        self::assertStringContainsString('| **Paged listings** | 2, 1 of them with a documented maximum page size |', $readme);
        self::assertStringContainsString('| **Experimental** | 1 operations documented only by the developer portal |', $readme);
        self::assertStringContainsString('[`openprovider/api-documentation@0123456789`](https://github.com/openprovider/api-documentation/tree/0123456789abcdef) and the developer portal, retrieved 2026-11-09', $readme);
        self::assertStringContainsString('| **Tested on** | PHP 8.2, 8.5, each with the lowest and the highest dependencies |', $readme);
        self::assertStringContainsString('| | One HTTP contract test for each of the 3 operations:', $readme);
        self::assertStringContainsString('| **S3** models | Each of the 10 models round-trips', $readme);
        self::assertStringContainsString('| **S4** tests | Line coverage of at least 95% and a mutation score (MSI) of at least 85.5% |', $readme);
        self::assertStringContainsString('| **S5** types | PHPStan at level max and Psalm at level 1, both with no baseline |', $readme);
        self::assertStringContainsString('| **S6** compatibility | PHP 8.2, 8.5, each with the lowest and the highest dependencies |', $readme);
    }

    public function testTheReferenceHasOneCollapsibleTablePerService(): void
    {
        $readme = self::render(self::regions());

        self::assertStringContainsString(<<<'MD'
            <details>
            <summary><code>$openProvider->dns()</code> · DNS &amp; more · 2 operations</summary>

            | Method | Endpoint | Summary |
            |---|---|---|
            | `listZones()` | `GET /dns/zones` | List zones <sub>paged, max 500</sub> |
            | `getZone()` | `GET /dns/zones/{name}` | Get a zone \| with &lt;records&gt; |

            </details>

            <details>
            <summary><code>$openProvider->auth()</code> · Auth · 1 operation</summary>
            MD, $readme);
        self::assertStringContainsString('| `signUp()` | `POST /resellers/signup` | <sub>paged · experimental</sub> |', $readme);
    }

    public function testTheErrorTableCountsTheCodesOfEachClass(): void
    {
        $readme = self::render(self::regions());

        self::assertStringContainsString('| `AuthenticationException` | The login was refused', $readme);
        self::assertStringContainsString('after one automatic re-login on code 196). | 2 |', $readme);
        self::assertStringContainsString('| `ValidationException` | The API refused the input. | 7 |', $readme);
        self::assertStringContainsString('| `ApiException` | Any other API error; the base class of the five above. | 3 |', $readme);
        self::assertStringContainsString('| `InvalidArgumentException` | An argument the spec does not allow, caught before anything is sent. | — |', $readme);
    }

    public function testRenderingTwiceChangesNothing(): void
    {
        $once = self::render(self::regions());

        self::assertSame($once, self::render($once));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function brokenRegions(): iterable
    {
        yield 'a region missing' => [str_replace("<!-- generated:errors -->\n<!-- /generated:errors -->\n", '', self::regions()), 'exactly one <!-- generated:errors -->'];
        yield 'a region twice' => [self::regions() . "<!-- generated:glance -->\n<!-- /generated:glance -->\n", 'exactly one <!-- generated:glance -->'];
        yield 'closed before opened' => [str_replace("<!-- generated:badges -->\n<!-- /generated:badges -->", "<!-- /generated:badges -->\n<!-- generated:badges -->", self::regions()), 'in that order'];
        yield 'an unknown region' => [self::regions() . "<!-- generated:extra -->\n<!-- /generated:extra -->\n", 'unknown region "extra"'];
    }

    #[DataProvider('brokenRegions')]
    public function testBrokenRegionsAreRefused(string $readme, string $message): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage($message);

        self::render($readme);
    }

    public function testTheGateSettingsAreReadFromTheirFiles(): void
    {
        self::assertSame(['8.2', '8.3', '8.10'], ReadmeInventory::phpVersions("jobs:\n      matrix:\n        php: ['8.10', '8.2', \"8.3\"]\n"));
        self::assertSame(85.0, ReadmeInventory::minMsi("{\n    // comment\n    \"minMsi\": 85\n}"));
        self::assertSame(87.5, ReadmeInventory::minMsi('  "minMsi": 87.5,'));
        self::assertSame(1, ReadmeInventory::psalmLevel('<psalm errorLevel="1" resolveFromConfigFile="true">'));
        self::assertSame('max', ReadmeInventory::phpstanLevel("parameters:\n    level: max\n"));
        self::assertSame('9', ReadmeInventory::phpstanLevel("parameters:\n    level: 9\n"));
    }

    /**
     * @return iterable<string, array{\Closure(): mixed, string}>
     */
    public static function missingSettings(): iterable
    {
        yield 'no php matrix' => [static fn (): mixed => ReadmeInventory::phpVersions('jobs: {}'), 'ci.yml has no `php: [...]` matrix'];
        yield 'a php matrix of names' => [static fn (): mixed => ReadmeInventory::phpVersions("php: ['latest']"), 'the php matrix holds something other than versions'];
        yield 'no minimum MSI' => [static fn (): mixed => ReadmeInventory::minMsi('{}'), 'infection.json5 sets no minMsi'];
        yield 'no Psalm level' => [static fn (): mixed => ReadmeInventory::psalmLevel('<psalm/>'), 'psalm.xml sets no errorLevel'];
        yield 'no PHPStan level' => [static fn (): mixed => ReadmeInventory::phpstanLevel('parameters: {}'), 'phpstan.neon sets no level'];
        yield 'categories that are not a map' => [static fn (): mixed => ReadmeInventory::errorCategories('x', 1), 'ErrorCatalog::CATEGORIES is not a map of codes'];
        yield 'a category that is not an ErrorCategory' => [static fn (): mixed => ReadmeInventory::errorCategories([5 => 'validation'], 1), 'classifies 5 as something other than an ErrorCategory'];
    }

    /**
     * @param \Closure(): mixed $read
     */
    #[DataProvider('missingSettings')]
    public function testAMissingSettingIsAnError(\Closure $read, string $message): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage($message);

        $read();
    }

    public function testCodesTheCatalogDoesNotClassifyAreOther(): void
    {
        self::assertSame(
            ['authentication' => 1, 'rateLimited' => 0, 'maintenance' => 0, 'notFound' => 0, 'validation' => 2, 'other' => 7],
            ReadmeInventory::errorCategories([1 => ErrorCategory::Authentication, 2 => ErrorCategory::Validation, 3 => ErrorCategory::Validation], 10),
        );
    }

    public function testTheReadmeIsUpToDate(): void
    {
        $readme = (string) file_get_contents(SpecOperations::ROOT . '/README.md');

        self::assertSame($readme, ReadmeGenerator::render($readme, ReadmeInventory::fromCode(SpecOperations::ROOT)), 'README.md is out of date: run php tools/generate readme');
        self::assertStringContainsString('| `retrieveWhiteLabelDomain()` | `POST /mailcow/white-labels/domain` | Retrieve white label domain <sub>experimental</sub> |', $readme);
        self::assertStringContainsString('| `listDomains()` | `GET /domains` | List domains <sub>paged, max 1000</sub> |', $readme);
        self::assertStringContainsString('[![license: MIT](https://img.shields.io/badge/license-MIT-0366D6)](LICENSE)', $readme);
    }

    public function testTheInventoryOfThisRepositoryMatchesTheCode(): void
    {
        $inventory = ReadmeInventory::fromCode(SpecOperations::ROOT);

        self::assertSame(count(SpecOperations::all()), $inventory->operationCount());
        self::assertSame($inventory->specOperations, $inventory->operationCount());
        self::assertCount(count(SpecOperations::services()), $inventory->services);
        self::assertSame(['8.2', '8.3', '8.4', '8.5'], $inventory->phpVersions);
        self::assertSame(1, $inventory->psalmLevel);
        self::assertSame('max', $inventory->phpstanLevel);
    }

    private static function render(string $readme): string
    {
        return ReadmeGenerator::render($readme, new ReadmeInventory(
            [
                ['accessor' => 'dns', 'group' => 'DNS & more', 'operations' => [
                    ['method' => 'listZones', 'http' => 'GET', 'path' => '/dns/zones', 'summary' => 'List zones', 'experimental' => false, 'paged' => true, 'maxLimit' => 500],
                    ['method' => 'getZone', 'http' => 'GET', 'path' => '/dns/zones/{name}', 'summary' => "Get a zone |\n with <records>", 'experimental' => false, 'paged' => false, 'maxLimit' => null],
                ]],
                ['accessor' => 'auth', 'group' => 'Auth', 'operations' => [
                    ['method' => 'signUp', 'http' => 'POST', 'path' => '/resellers/signup', 'summary' => '', 'experimental' => true, 'paged' => true, 'maxLimit' => null],
                ]],
            ],
            3,
            10,
            2,
            12,
            ['authentication' => 2, 'validation' => 7, 'other' => 3],
            '0123456789abcdef',
            '2026-11-09',
            ['8.2', '8.5'],
            95.0,
            85.5,
            1,
            'max',
        ));
    }

    private static function regions(): string
    {
        return implode('', array_map(
            static fn (string $name): string => "<!-- generated:$name -->\n<!-- /generated:$name -->\n",
            ReadmeGenerator::REGIONS,
        ));
    }
}
