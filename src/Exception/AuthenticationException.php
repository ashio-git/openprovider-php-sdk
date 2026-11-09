<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Exception;

/**
 * Bad credentials, an expired token that a new login did not fix, a blocked IP (10005) or a required 2FA (10006).
 */
final class AuthenticationException extends ApiException
{
}
