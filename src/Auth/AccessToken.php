<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Auth;

/**
 * A bearer token from POST /auth/login. `expiresAt` is when the SDK stops using it, a little
 * before the API's 48-hour lifetime ends.
 */
final class AccessToken implements \Stringable
{
    public function __construct(
        public readonly Secret $token,
        public readonly ?int $resellerId,
        public readonly \DateTimeImmutable $expiresAt,
    ) {
    }

    public function isUsableAt(\DateTimeImmutable $now): bool
    {
        return $now < $this->expiresAt;
    }

    #[\Override]
    public function __toString(): string
    {
        return sprintf(
            'AccessToken(token=%s, resellerId=%s, expiresAt=%s)',
            $this->token,
            $this->resellerId ?? 'null',
            $this->expiresAt->format(\DateTimeInterface::ATOM),
        );
    }
}
