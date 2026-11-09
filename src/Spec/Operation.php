<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Spec;

/**
 * Marks the method that implements one operation of spec/openprovider.json (plan A8). The
 * registry test reads it to prove that every operation has exactly one method and that every
 * public method of a service is an operation.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class Operation
{
    /**
     * @param string $method      the HTTP method, upper case
     * @param string $path        the spec path, relative to the base path (`/domains/{id}`)
     * @param string $operationId the spec's operationId
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly string $operationId,
    ) {
    }
}
