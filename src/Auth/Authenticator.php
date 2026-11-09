<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Auth;

use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Exception\ExceptionFactory;
use Ashio\OpenProvider\Exception\UnexpectedResponseException;
use Ashio\OpenProvider\Http\HttpTransport;
use Ashio\OpenProvider\Response\ApiResponse;
use Ashio\OpenProvider\Support\Clock;

/**
 * Lazy login: nothing happens until a token is needed. Tokens are kept in a TokenStore and
 * reused until shortly before the API's 48-hour lifetime ends.
 */
final class Authenticator
{
    /** The API's token lifetime (spec/behavior.json authentication.tokenTtlSeconds). */
    public const TOKEN_TTL_SECONDS = 172800;

    /** When the SDK stops using a token and logs in again: one hour before it expires. */
    public const REFRESH_AFTER_SECONDS = 169200;

    public const LOGIN_PATH = '/auth/login';

    private readonly string $key;

    public function __construct(
        private readonly ClientConfig $config,
        private readonly Credentials $credentials,
        private readonly HttpTransport $transport,
        private readonly TokenStore $store,
        private readonly Clock $clock,
    ) {
        $this->key = hash('sha256', $config->environment->value . "\0" . $credentials->username);
    }

    /**
     * @phpstan-impure logs in when the stored token is missing or due for renewal
     */
    public function token(): AccessToken
    {
        $token = $this->store->get($this->key);
        if ($token !== null && $token->isUsableAt($this->clock->now())) {
            return $token;
        }

        return $this->login();
    }

    /**
     * Forgets a token the API rejected. A newer token saved meanwhile (by another process
     * sharing the store) is kept.
     */
    public function invalidate(AccessToken $stale): void
    {
        $current = $this->store->get($this->key);
        if ($current !== null && hash_equals($current->token->reveal(), $stale->token->reveal())) {
            $this->store->delete($this->key);
        }
    }

    private function login(): AccessToken
    {
        $body = ['username' => $this->credentials->username, 'password' => $this->credentials->password->reveal()];
        if ($this->config->ip !== null) {
            $body['ip'] = $this->config->ip;
        }
        $http = $this->transport->send(
            'POST',
            $this->config->baseUrl() . self::LOGIN_PATH,
            [],
            json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        );
        $response = ApiResponse::fromHttp($http->status, $http->body);
        if (!$response->isSuccess()) {
            throw ExceptionFactory::fromResponse($response);
        }

        $data = is_array($response->data) ? $response->data : [];
        if (!isset($data['token']) || !is_string($data['token']) || $data['token'] === '') {
            throw new UnexpectedResponseException('The login response has no token', $response->httpStatus);
        }
        $accessToken = new AccessToken(
            new Secret($data['token']),
            isset($data['reseller_id']) && is_int($data['reseller_id']) ? $data['reseller_id'] : null,
            $this->clock->now()->modify(sprintf('+%d seconds', self::REFRESH_AFTER_SECONDS)),
        );
        $this->store->save($this->key, $accessToken);

        return $accessToken;
    }
}
