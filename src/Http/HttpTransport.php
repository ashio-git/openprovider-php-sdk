<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Http;

use Ashio\OpenProvider\Exception\TransportException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Sends one HTTP request through any PSR-18 client. It never interprets the status: the API
 * answers errors with HTTP 200, 401 or 500 alike, so the envelope decides.
 */
final class HttpTransport
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requests,
        private readonly StreamFactoryInterface $streams,
        private readonly string $userAgent,
    ) {
    }

    /**
     * @param array<string, string> $headers may carry the bearer token, hence sensitive
     * @param string|null           $body    may carry the password, hence sensitive
     */
    public function send(
        string $method,
        string $url,
        #[\SensitiveParameter]
        array $headers = [],
        #[\SensitiveParameter]
        ?string $body = null,
    ): HttpResponse {
        $request = $this->requests->createRequest($method, $url)
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', $this->userAgent);
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        if ($body !== null) {
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streams->createStream($body));
        }

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            // Not chained as previous: PSR-18 exceptions may hold the request, and with it the
            // bearer token or the login body.
            throw new TransportException(sprintf('%s %s failed (%s): %s', $method, $url, $e::class, $e->getMessage()));
        }

        return new HttpResponse($response->getStatusCode(), (string) $response->getBody());
    }
}
