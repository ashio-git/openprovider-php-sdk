<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Exception;

/**
 * The caller passed something the SDK or the spec does not allow. Raised before any request.
 */
final class InvalidArgumentException extends \InvalidArgumentException implements OpenProviderException
{
}
