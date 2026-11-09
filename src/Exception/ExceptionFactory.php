<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Exception;

use Ashio\OpenProvider\Response\ApiResponse;

final class ExceptionFactory
{
    /**
     * @param ApiResponse<mixed> $response
     */
    public static function fromResponse(ApiResponse $response): ApiException
    {
        if ($response->isSuccess()) {
            throw new \LogicException('A response with code 0 is not an error');
        }
        $category = ErrorCatalog::category($response->code);
        if ($category === ErrorCategory::Other && $response->maintenance) {
            $category = ErrorCategory::Maintenance;
        }
        $class = match ($category) {
            ErrorCategory::Authentication => AuthenticationException::class,
            ErrorCategory::RateLimited => RateLimitedException::class,
            ErrorCategory::Maintenance => MaintenanceException::class,
            ErrorCategory::NotFound => NotFoundException::class,
            ErrorCategory::Validation => ValidationException::class,
            ErrorCategory::Other => ApiException::class,
        };

        return new $class(
            $response->code,
            $response->httpStatus,
            $response->desc,
            $response->data,
            $response->warnings,
            $response->maintenance,
        );
    }
}
