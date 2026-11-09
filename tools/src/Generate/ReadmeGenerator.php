<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Generate;

use Ashio\OpenProvider\Exception\ErrorCategory;
use Ashio\OpenProvider\Tools\Spec\SpecException;

/**
 * Fills the generated regions of README.md (gate S8): the badges, the "at a glance" numbers, the
 * reference of every operation and the error table. The prose around them is written by hand and
 * kept as it is. A region is delimited by `<!-- generated:NAME -->` and `<!-- /generated:NAME -->`;
 * every region must be there exactly once, and no other may be.
 */
final class ReadmeGenerator
{
    public const REGIONS = ['badges', 'glance', 'services', 'errors', 'gates'];

    private const REPOSITORY = 'https://github.com/ashio-git/openprovider-php-sdk';

    /** What each exception means, in the order the table lists them. */
    private const EXCEPTIONS = [
        ErrorCategory::Authentication->value => ['AuthenticationException', 'The login was refused or the token is not accepted (after one automatic re-login on code 196).'],
        ErrorCategory::RateLimited->value => ['RateLimitedException', 'A rate limit or a temporary block. Never retried automatically.'],
        ErrorCategory::Maintenance->value => ['MaintenanceException', 'The API is in maintenance. Reads may be retried if you opt in.'],
        ErrorCategory::NotFound->value => ['NotFoundException', 'The object does not exist.'],
        ErrorCategory::Validation->value => ['ValidationException', 'The API refused the input.'],
        ErrorCategory::Other->value => ['ApiException', 'Any other API error; the base class of the five above.'],
    ];

    public static function render(string $readme, ReadmeInventory $inventory): string
    {
        preg_match_all('/<!-- (\/?)generated:([a-z-]+) -->/', $readme, $markers, PREG_SET_ORDER);
        $seen = [];
        foreach ($markers as [, $closing, $name]) {
            if (!in_array($name, self::REGIONS, true)) {
                throw new SpecException(sprintf('README.md has an unknown region "%s"', $name));
            }
            $seen[$name][] = $closing;
        }
        foreach (self::REGIONS as $name) {
            if (($seen[$name] ?? []) !== ['', '/']) {
                throw new SpecException(sprintf('README.md needs exactly one <!-- generated:%1$s --> … <!-- /generated:%1$s --> region, in that order', $name));
            }
        }

        foreach (self::REGIONS as $name) {
            $content = match ($name) {
                'badges' => self::badges($inventory),
                'glance' => self::glance($inventory),
                'services' => self::services($inventory),
                'errors' => self::errors($inventory),
                default => self::gates($inventory),
            };
            $readme = (string) preg_replace_callback(
                sprintf('/(<!-- generated:%1$s -->).*?(<!-- \/generated:%1$s -->)/s', $name),
                static fn (array $m): string => $m[1] . "\n" . $content . $m[2],
                $readme,
            );
        }

        return $readme;
    }

    private static function badges(ReadmeInventory $inventory): string
    {
        $php = $inventory->phpVersions;
        $operations = $inventory->operationCount();

        return implode("\n", [
            sprintf('[![CI](%1$s/actions/workflows/ci.yml/badge.svg)](%1$s/actions/workflows/ci.yml)', self::REPOSITORY),
            self::badge('PHP', $php[0] . ' – ' . $php[array_key_last($php)], '777BB4', 'php'),
            self::badge('API operations', sprintf('%d/%d', $operations, $inventory->specOperations), $operations === $inventory->specOperations ? '2EA44F' : 'D73A49'),
            self::badge('PHPStan', 'level ' . $inventory->phpstanLevel, '2EA44F'),
            self::badge('Psalm', 'level ' . $inventory->psalmLevel, '2EA44F'),
            self::badge('line coverage', '≥ ' . self::number($inventory->minCoverage) . '% gate', '2EA44F'),
            self::badge('mutation score', '≥ ' . self::number($inventory->minMsi) . '% gate', '2EA44F'),
            '[' . self::badge('license', 'MIT', '0366D6') . '](LICENSE)',
        ]) . "\n";
    }

    private static function glance(ReadmeInventory $inventory): string
    {
        $paged = 0;
        $capped = 0;
        $experimental = 0;
        foreach ($inventory->services as $service) {
            foreach ($service['operations'] as $operation) {
                $paged += $operation['paged'] ? 1 : 0;
                $capped += $operation['maxLimit'] !== null ? 1 : 0;
                $experimental += $operation['experimental'] ? 1 : 0;
            }
        }

        return implode("\n", [
            '| | |',
            '|---|---|',
            sprintf('| **Operations** | %d of the %d in the current API reference, in %d services |', $inventory->operationCount(), $inventory->specOperations, count($inventory->services)),
            sprintf('| **Models** | %d immutable, typed models and %d sets of enum values |', $inventory->models, $inventory->enums),
            sprintf('| **Error codes** | %d, each mapped to an exception class |', $inventory->errorCodes),
            sprintf('| **Paged listings** | %d, %d of them with a documented maximum page size |', $paged, $capped),
            sprintf('| **Experimental** | %d operations documented only by the developer portal |', $experimental),
            sprintf('| **Spec** | [`openprovider/api-documentation@%s`](https://github.com/openprovider/api-documentation/tree/%s) and the developer portal, retrieved %s |', substr($inventory->specCommit, 0, 10), $inventory->specCommit, $inventory->specRetrieved),
            sprintf('| **Tested on** | PHP %s, each with the lowest and the highest dependencies |', implode(', ', $inventory->phpVersions)),
        ]) . "\n";
    }

    private static function services(ReadmeInventory $inventory): string
    {
        $blocks = [];
        foreach ($inventory->services as $service) {
            $count = count($service['operations']);
            $lines = [
                '<details>',
                sprintf('<summary><code>$openProvider->%s()</code> · %s · %d operation%s</summary>', $service['accessor'], htmlspecialchars($service['group']), $count, $count === 1 ? '' : 's'),
                '',
                '| Method | Endpoint | Summary |',
                '|---|---|---|',
            ];
            foreach ($service['operations'] as $operation) {
                $notes = [];
                if ($operation['paged']) {
                    $notes[] = $operation['maxLimit'] === null ? 'paged' : sprintf('paged, max %d', $operation['maxLimit']);
                }
                if ($operation['experimental']) {
                    $notes[] = 'experimental';
                }
                $summary = self::cell($operation['summary']);
                $lines[] = sprintf(
                    '| `%s()` | `%s %s` | %s%s |',
                    $operation['method'],
                    $operation['http'],
                    $operation['path'],
                    $summary,
                    $notes === [] ? '' : ($summary === '' ? '' : ' ') . '<sub>' . implode(' · ', $notes) . '</sub>',
                );
            }
            $lines[] = '';
            $lines[] = '</details>';
            $blocks[] = implode("\n", $lines);
        }

        return implode("\n\n", $blocks) . "\n";
    }

    private static function errors(ReadmeInventory $inventory): string
    {
        $lines = [
            '| Exception | Thrown when | API codes |',
            '|---|---|---|',
        ];
        foreach (self::EXCEPTIONS as $category => [$class, $meaning]) {
            $lines[] = sprintf('| `%s` | %s | %d |', $class, $meaning, $inventory->errorCategories[$category] ?? 0);
        }
        $lines[] = '| `TransportException` | The HTTP request itself failed (network, DNS, TLS, timeout). | — |';
        $lines[] = '| `UnexpectedResponseException` | The API answered with something that is not a JSON envelope, or data of another type than the spec declares. | — |';
        $lines[] = '| `InvalidArgumentException` | An argument the spec does not allow, caught before anything is sent. | — |';

        return implode("\n", $lines) . "\n";
    }

    private static function gates(ReadmeInventory $inventory): string
    {
        $php = $inventory->phpVersions;

        return implode("\n", [
            '| Gate | What it guarantees | How |',
            '|---|---|---|',
            '| **S1** registry | Every operation in the spec has exactly one method, and no public method is anything else | `tests/Service/OperationRegistryTest.php` |',
            '| **S2** conformance | No divergence between what each method sends and reads and what the spec declares; the audit is itself tested against bugs planted from 2.x | `composer conformance` |',
            sprintf('| | One HTTP contract test for each of the %d operations: method, path, query, body, token and response mapping | `tests/Service/OperationContractTest.php` |', $inventory->operationCount()),
            sprintf('| **S3** models | Each of the %d models round-trips seeded random payloads built from its schema, plus edge cases | `tests/Model/ModelRoundTripTest.php` |', $inventory->models),
            sprintf('| **S4** tests | Line coverage of at least %s%% and a mutation score (MSI) of at least %s%% | `composer test:coverage`, Infection |', self::number($inventory->minCoverage), self::number($inventory->minMsi)),
            sprintf('| **S5** types | PHPStan at level %s and Psalm at level %d, both with no baseline | `composer quality` |', $inventory->phpstanLevel, $inventory->psalmLevel),
            sprintf('| **S6** compatibility | PHP %s, each with the lowest and the highest dependencies | CI matrix |', implode(', ', $php)),
            '| **S7** reality | The SDK against the real sandbox | `composer smoke`, run manually |',
            '| **S8** honesty | The badges, the counts, the API reference and the error and gate tables of this README are generated, and every example in it is executed | `composer spec:verify`, `tests/Readme` |',
        ]) . "\n";
    }

    /**
     * A static shields.io badge: in its path `-` and `_` are doubled and the rest is URL-encoded.
     */
    private static function badge(string $label, string $message, string $color, ?string $logo = null): string
    {
        $escape = static fn (string $text): string => rawurlencode(str_replace(['-', '_'], ['--', '__'], $text));

        return sprintf(
            '![%s: %s](https://img.shields.io/badge/%s-%s-%s%s)',
            $label,
            $message,
            $escape($label),
            $escape($message),
            $color,
            $logo === null ? '' : '?logo=' . $logo . '&logoColor=white',
        );
    }

    /**
     * Spec text in one table cell: one line, pipes and HTML escaped.
     */
    private static function cell(string $text): string
    {
        $text = trim((string) preg_replace('/\s+/', ' ', $text));

        return str_replace('|', '\|', htmlspecialchars($text, ENT_NOQUOTES));
    }

    private static function number(float $value): string
    {
        return rtrim(rtrim(sprintf('%.2f', $value), '0'), '.');
    }
}
