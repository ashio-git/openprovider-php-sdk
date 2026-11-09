<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * spec/sources.json: where each input of the merge comes from and where it is stored.
 */
final class SourceManifest
{
    public const FILE = 'sources.json';
    public const SERVICE_DIR = 'upstream/api-documentation';
    public const PORTAL_FILE = 'upstream/developer-portal/swagger.spec.js';

    /**
     * @param array<array-key, mixed> $data the whole manifest, kept to write it back unchanged
     * @param list<string>            $services
     */
    private function __construct(
        public readonly array $data,
        public readonly string $repository,
        public readonly string $branch,
        public readonly string $commit,
        public readonly string $directory,
        public readonly array $services,
        public readonly string $portalUrl,
        public readonly string $portalVariable,
    ) {
    }

    public static function load(string $specDir): self
    {
        $path = $specDir . '/' . self::FILE;
        $contents = @file_get_contents($path);
        if ($contents === false) {
            throw new SpecException('Cannot read ' . $path);
        }

        return self::fromArray(Json::object(Json::decode($contents, self::FILE), self::FILE));
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $services = Json::object($data['services'] ?? null, 'sources.services');
        $portal = Json::object($data['portal'] ?? null, 'sources.portal');
        $files = array_map(
            static fn (mixed $name): string => Json::string($name, 'sources.services.files[]'),
            Json::list($services['files'] ?? null, 'sources.services.files'),
        );
        $commit = Json::string($services['commit'] ?? null, 'sources.services.commit');
        if (preg_match('/^[0-9a-f]{40}$/', $commit) !== 1) {
            throw new SpecException('sources.services.commit must be a full 40-character commit SHA');
        }

        return new self(
            $data,
            Json::string($services['repository'] ?? null, 'sources.services.repository'),
            Json::string($services['branch'] ?? null, 'sources.services.branch'),
            $commit,
            Json::string($services['directory'] ?? null, 'sources.services.directory'),
            $files,
            Json::string($portal['url'] ?? null, 'sources.portal.url'),
            Json::string($portal['variable'] ?? null, 'sources.portal.variable'),
        );
    }

    public function servicePath(string $service): string
    {
        return sprintf('%s/%s/%s.swagger.json', self::SERVICE_DIR, $this->directory, $service);
    }

    public function serviceUrl(string $service, ?string $ref = null): string
    {
        return sprintf(
            'https://raw.githubusercontent.com/%s/%s/%s/%s.swagger.json',
            $this->repository,
            $ref ?? $this->commit,
            $this->directory,
            $service,
        );
    }

    public function listingUrl(): string
    {
        return sprintf('https://api.github.com/repos/%s/contents/%s?ref=%s', $this->repository, $this->directory, $this->branch);
    }

    /**
     * @return array<string, string> local path => description, for every pinned input
     */
    public function inputPaths(): array
    {
        $paths = [];
        foreach ($this->services as $service) {
            $paths[$this->servicePath($service)] = $service;
        }
        $paths[self::PORTAL_FILE] = 'portal';

        return $paths;
    }

    public function portalEtag(): ?string
    {
        return Json::optionalString(Json::object($this->data['portal'] ?? [], 'sources.portal'), 'etag');
    }

    public function retrieved(): string
    {
        return Json::string($this->data['retrieved'] ?? null, 'sources.retrieved');
    }
}
