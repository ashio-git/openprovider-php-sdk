<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Spec;

use Ashio\OpenProvider\Tools\Spec\Sha256Sums;
use Ashio\OpenProvider\Tools\Spec\SpecException;
use PHPUnit\Framework\TestCase;

final class Sha256SumsTest extends TestCase
{
    private const A = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
    private const B = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';

    public function testParsesAndRendersTheCoreutilsFormatSortedByPath(): void
    {
        $sums = Sha256Sums::parse(self::B . "  z.json\n" . self::A . "  a/b.json\n");

        self::assertSame(self::A, $sums->get('a/b.json'));
        self::assertSame(self::A . "  a/b.json\n" . self::B . "  z.json\n", $sums->render());
    }

    public function testWithReplacesOneEntryAndKeepsTheOthers(): void
    {
        $sums = Sha256Sums::parse(self::A . "  x\n" . self::A . "  y\n")->with('x', self::B);

        self::assertSame(self::B, $sums->get('x'));
        self::assertSame(self::A, $sums->get('y'));
    }

    public function testUnknownPathHasNoSum(): void
    {
        self::assertNull(Sha256Sums::parse('')->get('missing'));
    }

    public function testRejectsMalformedLines(): void
    {
        $this->expectException(SpecException::class);

        Sha256Sums::parse("not-a-hash  file\n");
    }

    public function testRejectsDuplicatePaths(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('x');

        Sha256Sums::parse(self::A . "  x\n" . self::B . "  x\n");
    }
}
