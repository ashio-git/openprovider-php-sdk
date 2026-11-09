<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Auth;

/**
 * The API user. The password is a Secret, so printing or dumping credentials shows only the
 * username.
 */
final class Credentials implements \Stringable
{
    public readonly Secret $password;

    public function __construct(
        public readonly string $username,
        #[\SensitiveParameter]
        string|Secret $password,
    ) {
        $this->password = $password instanceof Secret ? $password : new Secret($password);
    }

    #[\Override]
    public function __toString(): string
    {
        return sprintf('Credentials(username=%s, password=%s)', $this->username, $this->password);
    }
}
