<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Conformance;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The audit's transport: records every request and answers the operation's request with the
 * envelope it was given. A login the client makes on its own gets a token, unless the operation
 * being probed is the login itself, whose answer carries one anyway.
 */
final class ProbeHttpClient implements ClientInterface
{
    /** @var list<RequestInterface> */
    public array $requests = [];

    /**
     * @param array<array-key, mixed> $envelope
     */
    public function __construct(
        private readonly array $envelope,
        private readonly string $loginPath,
        private readonly bool $probingLogin,
    ) {
    }

    #[\Override]
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        $envelope = !$this->probingLogin && str_ends_with($request->getUri()->getPath(), $this->loginPath)
            ? ['code' => 0, 'desc' => '', 'data' => ['token' => 'probe-token', 'reseller_id' => 1]]
            : $this->envelope;

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($envelope, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
    }

    public function last(): ?RequestInterface
    {
        return $this->requests === [] ? null : $this->requests[count($this->requests) - 1];
    }
}
