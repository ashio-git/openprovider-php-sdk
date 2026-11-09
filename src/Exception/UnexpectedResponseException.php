<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Exception;

/**
 * An HTTP answer that is not the API's JSON envelope, such as a proxy's HTML error page.
 * The body is not part of the message: it may hold data the caller did not mean to log.
 */
final class UnexpectedResponseException extends \RuntimeException implements OpenProviderException
{
    public function __construct(string $message, public readonly int $httpStatus, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
