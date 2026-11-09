<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Downloads over HTTPS. A GITHUB_TOKEN, when set, is sent to api.github.com only, to lift
 * the anonymous rate limit of the weekly drift job.
 */
final class GuzzleFetcher implements Fetcher
{
    private readonly Client $client;

    public function __construct(private readonly ?string $githubToken = null)
    {
        $this->client = new Client(['timeout' => 60, 'connect_timeout' => 15, 'http_errors' => true]);
    }

    #[\Override]
    public function get(string $url): Fetched
    {
        if (!str_starts_with($url, 'https://')) {
            throw new SpecException('Refusing a non-HTTPS source: ' . $url);
        }
        $headers = ['User-Agent' => 'ashio-openprovider-php spec-sync'];
        if (str_starts_with($url, 'https://api.github.com/')) {
            $headers['Accept'] = 'application/vnd.github+json';
            if ($this->githubToken !== null && $this->githubToken !== '') {
                $headers['Authorization'] = 'Bearer ' . $this->githubToken;
            }
        }

        try {
            $response = $this->client->request('GET', $url, ['headers' => $headers]);
        } catch (GuzzleException $e) {
            throw new SpecException(sprintf('Could not download %s: %s', $url, $e->getMessage()), 0, $e);
        }

        return new Fetched(
            (string) $response->getBody(),
            $response->hasHeader('ETag') ? $response->getHeaderLine('ETag') : null,
            $response->hasHeader('Last-Modified') ? $response->getHeaderLine('Last-Modified') : null,
        );
    }
}
