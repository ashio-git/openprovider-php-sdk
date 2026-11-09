<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Smoke\Support;

use Ashio\OpenProvider\Support\Sleeper;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The smoke suite's transport: it spaces requests at least $intervalMs apart and refuses to send
 * more than $budget in one run, so the suite cannot trip the sandbox's rate limits
 * (spec/behavior.json `rateLimits`: identical calls are blocked, some until removed by hand).
 */
final class PacedHttpClient implements ClientInterface
{
    private ?float $last = null;
    private int $sent = 0;

    /**
     * @param \Closure(): float $now monotonic seconds
     */
    public function __construct(
        private readonly ClientInterface $inner,
        private readonly Sleeper $sleeper,
        private readonly \Closure $now,
        private readonly int $intervalMs = 1000,
        private readonly int $budget = 40,
    ) {
        if ($intervalMs < 0 || $budget < 1) {
            throw new \InvalidArgumentException('The interval cannot be negative and the budget must be at least 1');
        }
    }

    #[\Override]
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if ($this->sent >= $this->budget) {
            throw new \LogicException(sprintf('The smoke suite may send %d requests per run; %s %s would be one more', $this->budget, $request->getMethod(), $request->getUri()->getPath()));
        }
        if ($this->last !== null) {
            $waitMs = (int) ceil($this->intervalMs - ((($this->now)() - $this->last) * 1000));
            if ($waitMs > 0) {
                $this->sleeper->sleep($waitMs);
            }
        }
        ++$this->sent;

        try {
            return $this->inner->sendRequest($request);
        } finally {
            $this->last = ($this->now)();
        }
    }

    public function sent(): int
    {
        return $this->sent;
    }
}
