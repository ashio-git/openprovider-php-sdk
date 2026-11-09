<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * A difference the merge policy does not settle by union: two types for one field or
 * parameter, or one operationId used by two operations.
 */
final class Conflict
{
    public function __construct(
        public readonly string $id,
        public readonly string $detail,
        public readonly ?Resolution $resolution = null,
    ) {
    }

    public function isResolved(): bool
    {
        return $this->resolution !== null;
    }
}
