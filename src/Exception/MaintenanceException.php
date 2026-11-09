<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Exception;

/**
 * The API or a registry is in maintenance, or the envelope says maintenance=true.
 */
final class MaintenanceException extends ApiException
{
}
