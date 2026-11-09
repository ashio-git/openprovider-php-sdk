<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Auth;

/**
 * The default store: tokens live as long as the PHP process.
 */
final class InMemoryTokenStore implements TokenStore
{
    /** @var array<string, AccessToken> */
    private array $tokens = [];

    #[\Override]
    public function get(string $key): ?AccessToken
    {
        return $this->tokens[$key] ?? null;
    }

    #[\Override]
    public function save(string $key, AccessToken $token): void
    {
        $this->tokens[$key] = $token;
    }

    #[\Override]
    public function delete(string $key): void
    {
        unset($this->tokens[$key]);
    }
}
