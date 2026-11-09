<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

interface Fetcher
{
    /**
     * @throws SpecException when the resource cannot be downloaded
     */
    public function get(string $url): Fetched;
}
