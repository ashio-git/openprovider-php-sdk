<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * One justified decision from spec/overrides.json.
 */
final class Resolution
{
    public const SERVICE = 'service';
    public const PORTAL = 'portal';

    /**
     * @param string $use "service", "portal", or the new operationId for a rename
     */
    public function __construct(
        public readonly string $use,
        public readonly string $reason,
    ) {
    }
}
