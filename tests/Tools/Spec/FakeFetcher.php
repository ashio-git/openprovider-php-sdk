<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Spec;

use Ashio\OpenProvider\Tools\Spec\Fetched;
use Ashio\OpenProvider\Tools\Spec\Fetcher;
use Ashio\OpenProvider\Tools\Spec\SpecException;

final class FakeFetcher implements Fetcher
{
    /** @var list<string> */
    public array $requested = [];

    /**
     * @param array<string, string|Fetched> $responses url => body
     */
    public function __construct(public array $responses)
    {
    }

    public function get(string $url): Fetched
    {
        $this->requested[] = $url;
        $response = $this->responses[$url] ?? throw new SpecException('HTTP 404 for ' . $url);

        return is_string($response) ? new Fetched($response) : $response;
    }
}
