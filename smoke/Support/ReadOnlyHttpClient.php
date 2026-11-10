<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Smoke\Support;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The production smoke suite's guard: it runs against a real account, so every request other than
 * a GET is refused before it is sent, except the POSTs that only read. A test that tries to write
 * fails instead of changing the account.
 */
final class ReadOnlyHttpClient implements ClientInterface
{
    /**
     * The POSTs that change nothing: the login and the domain availability check.
     */
    public const READING_POSTS = ['/v1/auth/login', '/v1/domains/check'];

    public function __construct(private readonly ClientInterface $inner)
    {
    }

    #[\Override]
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        $path = $request->getUri()->getPath();
        if ($method !== 'GET' && !($method === 'POST' && in_array($path, self::READING_POSTS, true))) {
            throw new \LogicException(sprintf('The production smoke suite only reads; %s %s was refused before it was sent', $method, $path));
        }

        return $this->inner->sendRequest($request);
    }
}
