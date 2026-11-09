<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Client;

use Ashio\OpenProvider\Auth\AccessToken;
use Ashio\OpenProvider\Auth\Authenticator;
use Ashio\OpenProvider\Auth\Credentials;
use Ashio\OpenProvider\Auth\InMemoryTokenStore;
use Ashio\OpenProvider\Auth\TokenStore;
use Ashio\OpenProvider\Exception\ErrorCatalog;
use Ashio\OpenProvider\Exception\ErrorCategory;
use Ashio\OpenProvider\Exception\ExceptionFactory;
use Ashio\OpenProvider\Exception\TransportException;
use Ashio\OpenProvider\Exception\UnexpectedResponseException;
use Ashio\OpenProvider\Http\EncodedRequest;
use Ashio\OpenProvider\Http\HttpTransport;
use Ashio\OpenProvider\Http\RequestEncoder;
use Ashio\OpenProvider\Response\ApiResponse;
use Ashio\OpenProvider\Spec\OperationSpec;
use Ashio\OpenProvider\Support\Clock;
use Ashio\OpenProvider\Support\Sleeper;
use Ashio\OpenProvider\Support\SystemClock;
use Ashio\OpenProvider\Support\SystemSleeper;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Sends operations and returns the API envelope, or throws.
 *
 * - Authentication is lazy and the token is reused (see Authenticator).
 * - Code 196 (expired or invalid token): forget the token, log in again once, repeat the call
 *   once. This holds for writes too: a 196 means the API refused the call before running it
 *   (the rule below against repeating writes is about transient failures).
 * - Reads (GET) may be retried on network failures, non-JSON 5xx answers and maintenance
 *   codes, only when ClientConfig::$getRetry allows it. Writes are sent exactly once.
 */
final class ApiClient
{
    public const EXPIRED_TOKEN_CODE = 196;

    public function __construct(
        private readonly ClientConfig $config,
        private readonly HttpTransport $transport,
        private readonly Authenticator $authenticator,
        private readonly RequestEncoder $encoder = new RequestEncoder(),
        private readonly Sleeper $sleeper = new SystemSleeper(),
    ) {
    }

    /**
     * Wires the default pieces: Guzzle as the PSR-18 client, an in-memory token store and the
     * system clock. Any of them can be replaced. No request is sent here.
     */
    public static function create(
        ClientConfig $config,
        Credentials $credentials,
        ?TokenStore $tokenStore = null,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?Clock $clock = null,
        ?Sleeper $sleeper = null,
    ): self {
        $factory = new HttpFactory();
        $transport = new HttpTransport(
            $httpClient ?? new GuzzleClient(['timeout' => $config->timeout, 'connect_timeout' => $config->connectTimeout]),
            $requestFactory ?? $factory,
            $streamFactory ?? $factory,
            $config->userAgent,
        );
        $authenticator = new Authenticator(
            $config,
            $credentials,
            $transport,
            $tokenStore ?? new InMemoryTokenStore(),
            $clock ?? new SystemClock(),
        );

        return new self($config, $transport, $authenticator, new RequestEncoder(), $sleeper ?? new SystemSleeper());
    }

    /**
     * @param array<array-key, mixed> $arguments keyed by the spec's parameter names
     *
     * @return ApiResponse<mixed> a successful envelope (code 0); data as decoded
     */
    public function call(OperationSpec $operation, array $arguments = []): ApiResponse
    {
        $request = $this->encoder->encode($operation, $arguments);
        $loggedInAgain = false;
        while (true) {
            $token = $operation->requiresAuth ? $this->authenticator->token() : null;
            $response = $this->send($operation, $request, $token);
            if ($response->code === self::EXPIRED_TOKEN_CODE && $token !== null && !$loggedInAgain) {
                $this->authenticator->invalidate($token);
                $loggedInAgain = true;

                continue;
            }
            if (!$response->isSuccess()) {
                throw ExceptionFactory::fromResponse($response);
            }

            return $response;
        }
    }

    public function config(): ClientConfig
    {
        return $this->config;
    }

    /**
     * @return ApiResponse<mixed>
     */
    private function send(OperationSpec $operation, EncodedRequest $request, ?AccessToken $token): ApiResponse
    {
        $retries = $operation->isWrite() ? 0 : $this->config->getRetry->maxRetries;
        $headers = $token === null ? [] : ['Authorization' => 'Bearer ' . $token->token->reveal()];
        $url = $this->config->baseUrl() . $request->target;
        for ($attempt = 0; ; ++$attempt) {
            try {
                $http = $this->transport->send($request->method, $url, $headers, $request->body);
                $response = ApiResponse::fromHttp($http->status, $http->body);
                if ($attempt >= $retries || !self::isTransient($response)) {
                    return $response;
                }
            } catch (TransportException $e) {
                if ($attempt >= $retries) {
                    throw $e;
                }
            } catch (UnexpectedResponseException $e) {
                if ($attempt >= $retries || $e->httpStatus < 500) {
                    throw $e;
                }
            }
            $this->sleeper->sleep($this->config->getRetry->delayMs($attempt + 1));
        }
    }

    /**
     * @param ApiResponse<mixed> $response
     */
    private static function isTransient(ApiResponse $response): bool
    {
        return !$response->isSuccess()
            && ($response->maintenance || ErrorCatalog::category($response->code) === ErrorCategory::Maintenance);
    }
}
