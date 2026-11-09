<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

final class Fetched
{
    public function __construct(
        public readonly string $body,
        public readonly ?string $etag = null,
        public readonly ?string $lastModified = null,
    ) {
    }
}
