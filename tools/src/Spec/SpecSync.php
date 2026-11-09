<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * Keeps spec/upstream in step with its sources:
 *
 * - verify(): offline; every pinned input exists and matches spec/SHA256SUMS.
 * - update(): downloads the pinned commit (or a new one) and the portal bundle, then rewrites
 *   the files, their hashes and the retrieval metadata.
 * - checkUpstream(): compares the pin with what the upstream branch and the portal serve now.
 */
final class SpecSync
{
    public const SUMS = 'SHA256SUMS';
    public const MERGED = 'openprovider.json';

    public function __construct(
        private readonly string $specDir,
        private readonly Fetcher $fetcher,
    ) {
    }

    /**
     * @return list<string> problems; empty when every input is intact
     */
    public function verify(): array
    {
        $manifest = SourceManifest::load($this->specDir);
        $sums = $this->sums();
        $problems = [];
        foreach (array_keys($manifest->inputPaths()) as $path) {
            $expected = $sums->get($path);
            $file = $this->specDir . '/' . $path;
            if ($expected === null) {
                $problems[] = $path . ' has no entry in SHA256SUMS';
            } elseif (!is_file($file)) {
                $problems[] = $path . ' is missing';
            } elseif (hash_file('sha256', $file) !== $expected) {
                $problems[] = $path . ' does not match SHA256SUMS';
            }
        }
        $known = $manifest->inputPaths() + [self::MERGED => 'merge output'];
        foreach (array_keys($sums->all()) as $path) {
            if (!isset($known[$path])) {
                $problems[] = sprintf('SHA256SUMS lists %s, which no source declares', $path);
            }
        }

        return $problems;
    }

    /**
     * Downloads everything first and writes only when every download succeeded and parsed.
     */
    public function update(?string $commit, string $today): void
    {
        $manifest = SourceManifest::load($this->specDir);
        $data = $manifest->data;
        if ($commit !== null) {
            $services = Json::object($data['services'] ?? [], 'sources.services');
            $services['commit'] = $commit;
            $data['services'] = $services;
            $manifest = SourceManifest::fromArray($data);
        }

        $downloads = [];
        foreach ($manifest->services as $service) {
            $body = $this->fetcher->get($manifest->serviceUrl($service))->body;
            Json::decode($body, $service . '.swagger.json');
            $downloads[$manifest->servicePath($service)] = $body;
        }
        $portal = $this->fetcher->get($manifest->portalUrl);
        Json::decode(PortalBundle::extract($portal->body, $manifest->portalVariable), 'the portal bundle');
        $downloads[SourceManifest::PORTAL_FILE] = $portal->body;

        $sums = $this->sums();
        foreach ($downloads as $path => $body) {
            $file = $this->specDir . '/' . $path;
            if (!is_dir(dirname($file)) && !mkdir(dirname($file), 0o775, true) && !is_dir(dirname($file))) {
                throw new SpecException('Cannot create ' . dirname($file));
            }
            self::write($file, $body);
            $sums = $sums->with($path, hash('sha256', $body));
        }

        $portalData = Json::object($data['portal'] ?? [], 'sources.portal');
        unset($portalData['etag'], $portalData['lastModified']);
        if ($portal->etag !== null) {
            $portalData['etag'] = $portal->etag;
        }
        if ($portal->lastModified !== null) {
            $portalData['lastModified'] = $portal->lastModified;
        }
        $data['portal'] = $portalData;
        $data['retrieved'] = $today;

        self::write($this->specDir . '/' . self::SUMS, $sums->render());
        self::write($this->specDir . '/' . SourceManifest::FILE, Json::encode($data));
    }

    /**
     * @return list<string> what changed upstream since the pin; empty when nothing did
     */
    public function checkUpstream(): array
    {
        $manifest = SourceManifest::load($this->specDir);
        $sums = $this->sums();
        $drift = [];
        foreach ($manifest->services as $service) {
            $current = hash('sha256', $this->fetcher->get($manifest->serviceUrl($service, $manifest->branch))->body);
            if ($current !== $sums->get($manifest->servicePath($service))) {
                $drift[] = sprintf('%s/%s.swagger.json changed on %s since %s', $manifest->directory, $service, $manifest->branch, $manifest->commit);
            }
        }

        $listed = [];
        foreach (Json::list(Json::decode($this->fetcher->get($manifest->listingUrl())->body, 'the GitHub listing'), 'listing') as $entry) {
            $name = Json::string(Json::object($entry, 'listing entry')['name'] ?? null, 'listing entry name');
            if (str_ends_with($name, '.swagger.json')) {
                $listed[] = substr($name, 0, -strlen('.swagger.json'));
            }
        }
        $added = array_diff($listed, $manifest->services);
        sort($added);
        foreach ($added as $service) {
            $drift[] = sprintf('%s/%s.swagger.json was added on %s', $manifest->directory, $service, $manifest->branch);
        }
        foreach (array_diff($manifest->services, $listed) as $service) {
            $drift[] = sprintf('%s/%s.swagger.json was removed on %s', $manifest->directory, $service, $manifest->branch);
        }

        $portal = $this->fetcher->get($manifest->portalUrl);
        if (hash('sha256', $portal->body) !== $sums->get(SourceManifest::PORTAL_FILE)) {
            $drift[] = sprintf('the portal bundle changed (ETag %s → %s)', $manifest->portalEtag() ?? 'unknown', $portal->etag ?? 'unknown');
        }

        return $drift;
    }

    private function sums(): Sha256Sums
    {
        $file = $this->specDir . '/' . self::SUMS;

        return Sha256Sums::parse(is_file($file) ? (string) file_get_contents($file) : '');
    }

    private static function write(string $file, string $contents): void
    {
        if (file_put_contents($file, $contents) !== strlen($contents)) {
            throw new SpecException('Cannot write ' . $file);
        }
    }
}
