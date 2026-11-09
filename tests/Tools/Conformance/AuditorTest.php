<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Conformance;

use Ashio\OpenProvider\Model\ModelMap;
use Ashio\OpenProvider\Tests\Support\SpecOperations;
use Ashio\OpenProvider\Tests\Tools\Conformance\Fixture\BrokenAuthService;
use Ashio\OpenProvider\Tests\Tools\Conformance\Fixture\BrokenDnsService;
use Ashio\OpenProvider\Tests\Tools\Conformance\Fixture\BrokenResellerService;
use Ashio\OpenProvider\Tools\Conformance\Auditor;
use Ashio\OpenProvider\Tools\Conformance\ConformanceCommand;
use Ashio\OpenProvider\Tools\Conformance\Report;
use Ashio\OpenProvider\Tools\Generate\OperationGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * S2: the conformance audit of the study, as a gate. It runs every service method against a
 * recording transport and compares what goes out and what comes back with the spec. The real
 * services must audit clean; services with the 2.x SDK's bugs planted must not.
 */
final class AuditorTest extends TestCase
{
    private const LOGIN = ['POST /auth/login'];

    /** No group is deferred any more; the broken run defers one to keep the pending path covered. */
    private const DEFERRED = ['Mailcow' => 'deferred by this test'];

    private static ?Report $broken = null;

    public function testTheSdkHasNoDivergence(): void
    {
        $report = (new Auditor(SpecOperations::spec(), SpecOperations::services(), ModelMap::MODELS, OperationGenerator::DEFERRED_GROUPS, self::LOGIN))->run();

        self::assertSame([], array_map('strval', $report->divergences));
        self::assertTrue($report->isClean());
        self::assertCount(140, $report->conform);
        self::assertSame([], $report->pending);
    }

    public function testTheCommandExitsZeroOnTheSdkAndPrintsTheCounts(): void
    {
        $out = fopen('php://memory', 'w+');
        $err = fopen('php://memory', 'w+');
        self::assertIsResource($out);
        self::assertIsResource($err);

        $code = ConformanceCommand::run([], SpecOperations::ROOT, $out, $err);

        rewind($out);
        rewind($err);
        self::assertSame(0, $code, (string) stream_get_contents($err));
        self::assertStringContainsString('140 operations audited: 140 conform, 0 MAJOR, 0 MINOR; 0 pending', (string) stream_get_contents($out));
    }

    public function testTheCommandExitsTwoOnBadUsageOrAnUnreadableSpec(): void
    {
        $out = fopen('php://memory', 'w+');
        $err = fopen('php://memory', 'w+');
        self::assertIsResource($out);
        self::assertIsResource($err);

        self::assertSame(2, ConformanceCommand::run(['--verbose'], SpecOperations::ROOT, $out, $err));
        self::assertSame(2, ConformanceCommand::run([], sys_get_temp_dir() . '/no-such-root-' . bin2hex(random_bytes(4)), $out, $err));
        rewind($err);
        self::assertStringContainsString('Cannot read', (string) stream_get_contents($err));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function plantedDefects(): iterable
    {
        yield 'query filter sent in a DELETE body' => ['DeleteZone', 'query_sent_as_body'];
        yield 'body on an operation without one' => ['DeleteZone', 'body_not_in_spec'];
        yield 'argument that never reaches the server' => ['DeleteZone', 'param_ignored'];
        yield 'query parameter without an argument' => ['DeleteZone', 'missing_param'];
        yield 'untyped body' => ['CreateToken', 'body_model'];
        yield 'route the spec does not have' => ['GetZone', 'wrong_route'];
        yield 'camelCase filter' => ['ListCustomers', 'wrong_param_name'];
        yield 'false dropped by array_filter' => ['GetReseller', 'param_ignored'];
        yield 'raw data instead of the model' => ['GetReseller', 'raw_data'];
        yield 'string filter typed as integer' => ['DeleteTag', 'param_type'];
        yield 'two methods for one operation' => ['DeleteTag', 'duplicate_operation'];
        yield 'token sent with the login' => ['Login', 'auth'];
        yield 'portal-only operation not marked experimental' => ['CreateResellerPublic', 'experimental_undocumented'];
        yield 'data read into another model' => ['CreateResellerPublic', 'model_mismatch'];
        yield 'operation without a method' => ['GetPrice', 'missing_operation'];
        yield 'public method that is no operation' => [BrokenDnsService::class . '::helper', 'untraced_method'];
        yield 'one service, two tag groups' => [BrokenAuthService::class, 'mixed_service'];
    }

    #[DataProvider('plantedDefects')]
    public function testAPlantedDefectIsReported(string $operation, string $kind): void
    {
        $found = array_filter(self::broken()->divergences, static fn ($d): bool => $d->operation === $operation && $d->kind === $kind);

        self::assertNotSame([], $found, "no $kind for $operation in:\n" . self::broken()->render());
    }

    public function testTheBrokenServicesAreNotCleanAndTheRightOperationStillConforms(): void
    {
        $report = self::broken();

        self::assertFalse($report->isClean());
        // DeleteEasyDmarc itself is right; the defect is the service holding it (mixed_service).
        self::assertSame(['DeleteEasyDmarc', 'GetSettings'], $report->conform);
        self::assertSame(['CreateMailcowOrder'], array_keys($report->pending));
        self::assertStringContainsString('MAJOR', $report->render());
    }

    private static function broken(): Report
    {
        if (self::$broken === null) {
            $keep = ['DeleteZone', 'CreateToken', 'GetZone', 'ListCustomers', 'GetReseller', 'DeleteTag', 'Login', 'CreateResellerPublic', 'DeleteEasyDmarc', 'GetPrice', 'GetSettings', 'CreateMailcowOrder'];
            $spec = SpecOperations::spec();
            $paths = [];
            foreach (SpecOperations::all() as $id => $operation) {
                if (in_array($id, $keep, true)) {
                    $paths[$operation['path']][strtolower($operation['method'])] = $operation['operation'];
                }
            }
            $spec['paths'] = $paths;
            self::$broken = (new Auditor(
                $spec,
                [BrokenAuthService::class, BrokenDnsService::class, BrokenResellerService::class],
                ModelMap::MODELS,
                self::DEFERRED,
                self::LOGIN,
            ))->run();
        }

        return self::$broken;
    }
}
