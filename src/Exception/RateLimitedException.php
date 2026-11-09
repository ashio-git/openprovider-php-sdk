<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Exception;

/**
 * A call limit was reached (50) or the API blocked the account for excessive use (4007).
 */
final class RateLimitedException extends ApiException
{
}
