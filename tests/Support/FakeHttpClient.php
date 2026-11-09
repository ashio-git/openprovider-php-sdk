<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Support;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A PSR-18 client that answers from a queue and records every request.
 */
final class FakeHttpClient implements ClientInterface
{
    /** @var list<RequestInterface> */
    public array $requests = [];

    /** @var list<ResponseInterface|\Throwable> */
    private array $queue = [];

    /**
     * @param array<array-key, mixed> $envelope
     */
    public function reply(array $envelope, int $status = 200): self
    {
        $this->queue[] = new Response($status, ['Content-Type' => 'application/json'], json_encode($envelope, JSON_THROW_ON_ERROR));

        return $this;
    }

    public function replyRaw(string $body, int $status = 200, string $contentType = 'text/html'): self
    {
        $this->queue[] = new Response($status, ['Content-Type' => $contentType], $body);

        return $this;
    }

    public function fail(\Throwable $error): self
    {
        $this->queue[] = $error;

        return $this;
    }

    public function login(string $token = 'tok-1', int $resellerId = 7): self
    {
        return $this->reply(['code' => 0, 'desc' => '', 'data' => ['token' => $token, 'reseller_id' => $resellerId]]);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        $next = array_shift($this->queue);
        if ($next === null) {
            throw new \LogicException('No response queued for ' . $request->getMethod() . ' ' . $request->getUri());
        }
        if ($next instanceof \Throwable) {
            throw $next;
        }

        return $next;
    }

    /**
     * @return list<string> "METHOD path?query" of each request
     */
    public function targets(): array
    {
        return array_map(static function (RequestInterface $r): string {
            $query = $r->getUri()->getQuery();

            return $r->getMethod() . ' ' . $r->getUri()->getPath() . ($query === '' ? '' : '?' . $query);
        }, $this->requests);
    }

    public function count(string $target): int
    {
        return count(array_filter($this->targets(), static fn (string $t): bool => $t === $target));
    }

    public function pending(): int
    {
        return count($this->queue);
    }
}
