<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Http;

final class EncodedRequest
{
    /**
     * @param string      $target path and query, relative to the base path
     * @param string|null $body   JSON, or null when the operation has no body
     */
    public function __construct(
        public readonly string $method,
        public readonly string $target,
        public readonly ?string $body,
    ) {
    }
}
