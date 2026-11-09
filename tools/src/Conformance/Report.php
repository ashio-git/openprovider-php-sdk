<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Conformance;

/**
 * The result of an audit: the divergences, the operations without any, and the operations of a
 * deferred tag group that have no method yet.
 */
final class Report
{
    /**
     * @param list<Divergence>      $divergences
     * @param list<string>          $conform     operationIds
     * @param array<string, string> $pending     operationId => why it has no method yet
     * @param int                   $audited     operations with a method, or that should have one
     */
    public function __construct(
        public readonly array $divergences,
        public readonly array $conform,
        public readonly array $pending,
        public readonly int $audited,
    ) {
    }

    public function isClean(): bool
    {
        return $this->divergences === [];
    }

    public function count(string $severity): int
    {
        return count(array_filter($this->divergences, static fn (Divergence $d): bool => $d->severity === $severity));
    }

    public function summary(): string
    {
        $reasons = array_values(array_unique($this->pending));

        return sprintf(
            '%d operations audited: %d conform, %d MAJOR, %d MINOR; %d pending%s',
            $this->audited,
            count($this->conform),
            $this->count(Divergence::MAJOR),
            $this->count(Divergence::MINOR),
            count($this->pending),
            $reasons === [] ? '' : ' (' . implode('; ', $reasons) . ')',
        );
    }

    public function render(): string
    {
        $lines = array_map('strval', $this->divergences);
        $lines[] = $this->summary();

        return implode("\n", $lines) . "\n";
    }
}
