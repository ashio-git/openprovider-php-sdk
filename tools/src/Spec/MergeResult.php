<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * The merged spec plus everything the policy decided along the way, for spec/conflicts.md.
 */
final class MergeResult
{
    /**
     * @param array<string, mixed>    $spec
     * @param array<string, Conflict> $conflicts            id => conflict, resolved or not
     * @param list<string>            $portalOnlyOperations taken from the portal and marked experimental
     * @param list<string>            $serviceOnlyOperations dropped: not among the portal's operations
     * @param list<string>            $additions            fields and parameters only the portal has
     * @param list<string>            $enumUnions           enums whose values were united
     */
    public function __construct(
        private readonly array $spec,
        private readonly array $conflicts,
        private readonly array $portalOnlyOperations,
        private readonly array $serviceOnlyOperations,
        private readonly array $additions,
        private readonly array $enumUnions,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function spec(): array
    {
        $unresolved = $this->unresolvedConflicts();
        if ($unresolved !== []) {
            throw new SpecException(sprintf(
                "The merge has %d unresolved conflict(s); settle each in spec/overrides.json:\n- %s",
                count($unresolved),
                implode("\n- ", array_map(static fn (Conflict $c): string => $c->id . ': ' . $c->detail, $unresolved)),
            ));
        }

        return $this->spec;
    }

    /**
     * @return array<string, Conflict>
     */
    public function conflicts(): array
    {
        return $this->conflicts;
    }

    /**
     * @return array<string, Conflict>
     */
    public function unresolvedConflicts(): array
    {
        return array_filter($this->conflicts, static fn (Conflict $c): bool => !$c->isResolved());
    }

    /**
     * @return list<string>
     */
    public function portalOnlyOperations(): array
    {
        return $this->portalOnlyOperations;
    }

    /**
     * @return list<string>
     */
    public function serviceOnlyOperations(): array
    {
        return $this->serviceOnlyOperations;
    }

    /**
     * @return list<string>
     */
    public function additions(): array
    {
        return $this->additions;
    }

    /**
     * @return list<string>
     */
    public function enumUnions(): array
    {
        return $this->enumUnions;
    }

    public function operationCount(): int
    {
        $count = 0;
        foreach (Json::objectMap($this->spec['paths'] ?? [], 'paths') as $item) {
            $count += count($item);
        }

        return $count;
    }

    public function definitionCount(): int
    {
        return count(Json::object($this->spec['definitions'] ?? [], 'definitions'));
    }
}
