<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Support;

final class SystemClock implements Clock
{
    #[\Override]
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
