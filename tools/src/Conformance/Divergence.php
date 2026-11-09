<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Conformance;

/**
 * One way the SDK differs from the spec. MAJOR loses or corrupts data, or sends a request the
 * API does not understand; MINOR narrows what the SDK can express without losing data.
 */
final class Divergence implements \Stringable
{
    public const MAJOR = 'MAJOR';
    public const MINOR = 'MINOR';

    /**
     * @param self::MAJOR|self::MINOR $severity
     * @param string                  $area      registry, params, body, response, auth or docs
     * @param string                  $operation the operationId, or the class or method when there is none
     */
    public function __construct(
        public readonly string $severity,
        public readonly string $area,
        public readonly string $kind,
        public readonly string $operation,
        public readonly string $message,
    ) {
    }

    #[\Override]
    public function __toString(): string
    {
        return sprintf('%s %s/%s %s: %s', $this->severity, $this->area, $this->kind, $this->operation, $this->message);
    }
}
