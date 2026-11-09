<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Exception;

use Ashio\OpenProvider\Tools\Generate\ErrorCatalogGenerator;
use Ashio\OpenProvider\Tools\Generate\GenerateCommand;
use PHPUnit\Framework\TestCase;

/**
 * src/Exception/ErrorCatalog.php is generated: regenerating it produces no diff.
 */
final class ErrorCatalogGenerationTest extends TestCase
{
    private const ROOT = __DIR__ . '/../..';

    public function testTheCommittedCatalogIsWhatTheGeneratorWrites(): void
    {
        $generated = ErrorCatalogGenerator::generate(
            (string) file_get_contents(self::ROOT . '/spec/error-codes.json'),
            (string) file_get_contents(self::ROOT . '/spec/behavior.json'),
        );

        self::assertSame($generated, file_get_contents(self::ROOT . '/src/Exception/ErrorCatalog.php'));
        self::assertStringContainsString('@generated', $generated);
    }

    public function testTheCommandChecksTheCommittedCatalogAndRejectsUnknownTargets(): void
    {
        $out = fopen('php://memory', 'w+');
        $err = fopen('php://memory', 'w+');
        self::assertIsResource($out);
        self::assertIsResource($err);

        self::assertSame(0, GenerateCommand::run(['errors', '--check'], self::ROOT, $out, $err));
        self::assertSame(2, GenerateCommand::run(['models'], self::ROOT, $out, $err));
        self::assertSame(2, GenerateCommand::run([], self::ROOT, $out, $err));
    }

    public function testACodeInTwoClassesIsRefused(): void
    {
        $codes = (string) json_encode(['codes' => ['196' => ['description' => 'x']]]);
        $behavior = (string) json_encode(['errorClasses' => [
            'authentication' => ['codes' => [196]],
            'validation' => ['codes' => [196]],
        ]]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('196');

        ErrorCatalogGenerator::generate($codes, $behavior);
    }

    public function testAClassifiedCodeMissingFromTheCatalogIsRefused(): void
    {
        $codes = (string) json_encode(['codes' => ['196' => ['description' => 'x']]]);
        $behavior = (string) json_encode(['errorClasses' => ['rateLimited' => ['codes' => [50]]]]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('50');

        ErrorCatalogGenerator::generate($codes, $behavior);
    }
}
