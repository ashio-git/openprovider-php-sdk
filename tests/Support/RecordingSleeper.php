<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Support;

use Ashio\OpenProvider\Support\Sleeper;

final class RecordingSleeper implements Sleeper
{
    /** @var list<int> */
    public array $sleeps = [];

    public function sleep(int $milliseconds): void
    {
        $this->sleeps[] = $milliseconds;
    }
}
