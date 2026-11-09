<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * spec/overrides.json: the only way to settle a conflict or a name collision the merge
 * refuses to decide on its own. Every entry needs a reason; an entry that matches nothing
 * fails the merge, so overrides cannot go stale silently.
 */
final class Overrides
{
    /**
     * @param array<string, Resolution> $conflicts    conflict id => resolution
     * @param array<string, Resolution> $operationIds "METHOD /path" => resolution whose `use` is the new operationId
     */
    private function __construct(
        public readonly array $conflicts = [],
        public readonly array $operationIds = [],
    ) {
    }

    public static function none(): self
    {
        return new self();
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $unknown = array_diff(array_map('strval', array_keys($data)), ['conflicts', 'operationIds', '$comment']);
        if ($unknown !== []) {
            throw new SpecException('overrides.json has unknown keys: ' . implode(', ', $unknown));
        }

        $conflicts = [];
        $entries = Json::object($data['conflicts'] ?? [], 'overrides.conflicts');
        foreach (array_keys($entries) as $id) {
            $entry = Json::object($entries[$id], "overrides.conflicts[$id]");
            $use = Json::string($entry['use'] ?? null, "overrides.conflicts[$id].use");
            if ($use !== Resolution::SERVICE && $use !== Resolution::PORTAL) {
                throw new SpecException(sprintf('overrides.conflicts[%s].use must be "service" or "portal", got "%s"', $id, $use));
            }
            $conflicts[(string) $id] = new Resolution($use, self::reason($entry, "overrides.conflicts[$id]"));
        }

        $operationIds = [];
        $entries = Json::object($data['operationIds'] ?? [], 'overrides.operationIds');
        foreach (array_keys($entries) as $key) {
            $entry = Json::object($entries[$key], "overrides.operationIds[$key]");
            $operationId = Json::string($entry['operationId'] ?? null, "overrides.operationIds[$key].operationId");
            if (preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $operationId) !== 1) {
                throw new SpecException(sprintf('overrides.operationIds[%s].operationId is not an identifier: %s', $key, $operationId));
            }
            $operationIds[(string) $key] = new Resolution($operationId, self::reason($entry, "overrides.operationIds[$key]"));
        }

        return new self($conflicts, $operationIds);
    }

    /**
     * @param array<array-key, mixed> $entry
     */
    private static function reason(array $entry, string $context): string
    {
        $reason = trim(Json::string($entry['reason'] ?? null, $context . '.reason'));
        if ($reason === '') {
            throw new SpecException($context . '.reason must explain the override');
        }

        return $reason;
    }
}
