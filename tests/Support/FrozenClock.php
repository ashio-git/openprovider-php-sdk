<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Support;

use Ashio\OpenProvider\Support\Clock;

final class FrozenClock implements Clock
{
    public function __construct(private \DateTimeImmutable $now = new \DateTimeImmutable('2026-11-09 12:00:00 UTC'))
    {
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }

    public function advance(int $seconds): void
    {
        $this->now = $this->now->modify(sprintf('%+d seconds', $seconds));
    }
}
