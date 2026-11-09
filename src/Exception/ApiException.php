<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Exception;

use Ashio\OpenProvider\Response\Warning;

/**
 * The API answered with a non-zero code. Classified by that code, never by the HTTP status,
 * which the API does not set reliably (an expired token is HTTP 500 in production and 401 in
 * the sandbox). `getCode()` returns the API code.
 */
class ApiException extends \RuntimeException implements OpenProviderException
{
    /**
     * @param list<Warning> $warnings
     */
    final public function __construct(
        public readonly int $apiCode,
        public readonly int $httpStatus,
        public readonly string $desc,
        public readonly mixed $data,
        public readonly array $warnings = [],
        public readonly bool $maintenance = false,
    ) {
        $description = $desc !== '' ? $desc : (ErrorCatalog::describe($apiCode) ?? 'no description');
        parent::__construct(sprintf('Openprovider API error %d: %s', $apiCode, $description), $apiCode);
    }

    /**
     * What the knowledge base advises for this code, when it says anything.
     */
    public function resolution(): ?string
    {
        return ErrorCatalog::resolution($this->apiCode);
    }
}
