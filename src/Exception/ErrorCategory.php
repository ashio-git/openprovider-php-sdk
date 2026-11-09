<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Exception;

/**
 * How an API error code is classified (spec/behavior.json, errorClasses).
 */
enum ErrorCategory: string
{
    case Authentication = 'authentication';
    case RateLimited = 'rateLimited';
    case Maintenance = 'maintenance';
    case NotFound = 'notFound';
    case Validation = 'validation';
    case Other = 'other';
}
