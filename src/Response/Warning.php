<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Response;

/**
 * An entry of the envelope's `warnings`: the call worked, but the API has something to say.
 */
final class Warning
{
    public function __construct(
        public readonly int $code,
        public readonly string $desc,
        public readonly mixed $data = null,
    ) {
    }
}
