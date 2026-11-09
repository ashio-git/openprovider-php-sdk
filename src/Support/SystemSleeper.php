<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Support;

final class SystemSleeper implements Sleeper
{
    #[\Override]
    public function sleep(int $milliseconds): void
    {
        if ($milliseconds > 0) {
            usleep($milliseconds * 1000);
        }
    }
}
