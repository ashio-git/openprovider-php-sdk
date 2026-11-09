<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Support;

interface Clock
{
    public function now(): \DateTimeImmutable;
}
