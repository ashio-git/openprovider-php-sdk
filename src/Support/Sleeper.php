<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Support;

interface Sleeper
{
    public function sleep(int $milliseconds): void;
}
