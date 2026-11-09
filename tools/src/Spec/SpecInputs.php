<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * Everything the merge reads from spec/: the pinned service files, the portal bundle and
 * spec/overrides.json.
 */
final class SpecInputs
{
    public const OVERRIDES = 'overrides.json';

    /**
     * @param array<string, mixed>    $services service => decoded Swagger
     * @param array<array-key, mixed> $portal
     * @param array<string, mixed>    $sources  provenance written into the merged spec
     */
    private function __construct(
        public readonly SourceManifest $manifest,
        public readonly array $services,
        public readonly array $portal,
        public readonly array $sources,
        public readonly Overrides $overrides,
    ) {
    }

    public static function load(string $specDir): self
    {
        $manifest = SourceManifest::load($specDir);
        $services = [];
        $hashes = [];
        foreach ($manifest->services as $service) {
            $bytes = self::read($specDir . '/' . $manifest->servicePath($service));
            $services[$service] = Json::decode($bytes, $service . '.swagger.json');
            $hashes[$service] = hash('sha256', $bytes);
        }
        $bundle = self::read($specDir . '/' . SourceManifest::PORTAL_FILE);
        $portal = Json::object(
            Json::decode(PortalBundle::extract($bundle, $manifest->portalVariable), 'the portal bundle'),
            'the portal bundle',
        );
        $overridesFile = $specDir . '/' . self::OVERRIDES;
        $overrides = is_file($overridesFile)
            ? Overrides::fromArray(Json::object(Json::decode(self::read($overridesFile), self::OVERRIDES), self::OVERRIDES))
            : Overrides::none();

        return new self($manifest, $services, $portal, [
            'services' => [
                'repository' => $manifest->repository,
                'commit' => $manifest->commit,
                'directory' => $manifest->directory,
                'sha256' => $hashes,
            ],
            'portal' => [
                'url' => $manifest->portalUrl,
                'sha256' => hash('sha256', $bundle),
            ],
        ], $overrides);
    }

    public function merge(?Overrides $overrides = null): MergeResult
    {
        return (new SpecMerger($overrides ?? $this->overrides))->merge($this->services, $this->portal, $this->sources);
    }

    private static function read(string $file): string
    {
        $contents = @file_get_contents($file);
        if ($contents === false) {
            throw new SpecException('Cannot read ' . $file);
        }

        return $contents;
    }
}
