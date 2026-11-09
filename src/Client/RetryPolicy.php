<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Client;

use Ashio\OpenProvider\Exception\InvalidArgumentException;

/**
 * Automatic retries for reads (GET) only: network failures, non-JSON 5xx answers and
 * maintenance codes. Off by default. Writes are never retried, because the API documents no
 * idempotency and blocks identical writes.
 */
final class RetryPolicy
{
    /**
     * @param list<int> $delaysMs wait before retry 1, 2, …; the last value repeats
     */
    public function __construct(
        public readonly int $maxRetries = 0,
        public readonly array $delaysMs = [250, 1000, 4000],
    ) {
        if ($maxRetries < 0) {
            throw new InvalidArgumentException('maxRetries cannot be negative');
        }
        if ($delaysMs === [] || array_filter($delaysMs, static fn (int $d): bool => $d < 0) !== []) {
            throw new InvalidArgumentException('delaysMs needs at least one non-negative delay');
        }
    }

    public static function none(): self
    {
        return new self(0);
    }

    /**
     * @param int $retry 1 for the first retry
     */
    public function delayMs(int $retry): int
    {
        $index = min(max($retry - 1, 0), array_key_last($this->delaysMs) ?? 0);

        return $this->delaysMs[$index] ?? 0;
    }
}
