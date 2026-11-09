<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * Merges the per-service Swagger files with the developer portal, following the source policy
 * of docs/plans/2026-11-09-sdk-api-v1.md §4:
 *
 * 1. Operations: exactly the portal's set. Service-only operations are dropped; portal-only
 *    operations come from the portal and are marked `x-experimental`.
 * 2. Definitions: named `<service>.<name>` (portal imports: `portal.<name>`), so two services
 *    can never overwrite each other's schema the way the composed upstream file does.
 * 3. A schema both sources describe gets the union of its fields and enum values. A type
 *    difference is a conflict that only spec/overrides.json can settle.
 * 4. Paths lose their `/v1` or `/v1beta` prefix; the merged spec declares `basePath: /v1`.
 *
 * Only definitions reachable from the operations are emitted. The output depends on the
 * inputs alone (no clock, no ordering of the service list), so equal inputs give equal bytes.
 */
final class SpecMerger
{
    public const PORTAL = 'portal';
    public const PORTAL_SOURCE = 'developer-portal';
    public const BASE_PATH = '/v1';

    private readonly Overrides $overrides;

    public function __construct(?Overrides $overrides = null)
    {
        $this->overrides = $overrides ?? Overrides::none();
    }

    /**
     * @param array<array-key, mixed> $services service name => decoded per-service Swagger 2.0 file
     * @param array<array-key, mixed> $sources  provenance copied into `x-openprovider-sources`
     */
    public function merge(array $services, mixed $portal, array $sources = []): MergeResult
    {
        return (new MergeRun($this->overrides))->run($services, $portal, $sources);
    }
}
