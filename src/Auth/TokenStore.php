<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Auth;

/**
 * Where tokens wait between calls. The key identifies environment and user; implementations
 * must treat it as opaque.
 */
interface TokenStore
{
    public function get(string $key): ?AccessToken;

    public function save(string $key, AccessToken $token): void;

    public function delete(string $key): void;
}
