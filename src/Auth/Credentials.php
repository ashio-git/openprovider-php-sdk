<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Auth;

use Ashio\OpenProvider\Exception\InvalidArgumentException;

/**
 * The API user. The password is a Secret, so printing or dumping credentials shows only the
 * username. Both must be valid UTF-8, because the login sends them as JSON; anything else is
 * refused here rather than at the first call.
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
        if (preg_match('//u', $username) !== 1) {
            throw new InvalidArgumentException('The username is not valid UTF-8; the login sends it as JSON');
        }
        if (preg_match('//u', $this->password->reveal()) !== 1) {
            throw new InvalidArgumentException('The password is not valid UTF-8; the login sends it as JSON');
        }
    }

    #[\Override]
    public function __toString(): string
    {
        return sprintf('Credentials(username=%s, password=%s)', $this->username, $this->password);
    }
}
