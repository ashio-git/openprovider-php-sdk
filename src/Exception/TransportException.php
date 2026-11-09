<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Exception;

/**
 * The request did not get an HTTP answer (DNS, TLS, connection, timeout).
 */
final class TransportException extends \RuntimeException implements OpenProviderException
{
}
