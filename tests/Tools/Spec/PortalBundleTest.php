<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Spec;

use Ashio\OpenProvider\Tools\Spec\PortalBundle;
use Ashio\OpenProvider\Tools\Spec\SpecException;
use PHPUnit\Framework\TestCase;

final class PortalBundleTest extends TestCase
{
    public function testExtractsTheSpecAssignedToTheWindowVariable(): void
    {
        $js = 'window.OP_API_SPEC = {"swagger":"2.0","paths":{}};' . "\n";

        self::assertSame('{"swagger":"2.0","paths":{}}', PortalBundle::extract($js, 'window.OP_API_SPEC'));
    }

    public function testTheTrailingSemicolonIsOptional(): void
    {
        self::assertSame('{"a":1}', PortalBundle::extract("window.OP_API_SPEC = {\"a\":1}\n", 'window.OP_API_SPEC'));
    }

    public function testRejectsAnyOtherScriptShape(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('window.OP_API_SPEC');

        PortalBundle::extract('fetch("x"); window.OP_API_SPEC = {"a":1};', 'window.OP_API_SPEC');
    }

    public function testRejectsTrailingCodeAfterTheAssignment(): void
    {
        $this->expectException(SpecException::class);

        PortalBundle::extract('window.OP_API_SPEC = {"a":1}; alert(1);', 'window.OP_API_SPEC');
    }
}
