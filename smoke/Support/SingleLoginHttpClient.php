<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Smoke\Support;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The production smoke suite's second guard: one login per run. Whatever the first login
 * answered (a token, code 196, an error page), a second one is refused before it is sent, so a
 * refused login is never repeated against the account, which repeated failures can block.
 */
final class SingleLoginHttpClient implements ClientInterface
{
    public const LOGIN_PATH = '/v1/auth/login';

    private bool $loggedIn = false;

    public function __construct(private readonly ClientInterface $inner)
    {
    }

    #[\Override]
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if (strtoupper($request->getMethod()) === 'POST' && $request->getUri()->getPath() === self::LOGIN_PATH) {
            if ($this->loggedIn) {
                throw new \LogicException('The production smoke suite logs in once per run; a second login was refused before it was sent');
            }
            $this->loggedIn = true;
        }

        return $this->inner->sendRequest($request);
    }
}
