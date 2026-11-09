<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Client;

/**
 * Where the API lives. A backed enum: `Environment::from('sandbox')` works and an unknown
 * name throws, so a typo can never fall through to production.
 */
enum Environment: string
{
    case Production = 'production';
    case Sandbox = 'sandbox';

    public function baseUri(): string
    {
        return match ($this) {
            self::Production => 'https://api.openprovider.eu',
            self::Sandbox => 'https://api.sandbox.openprovider.nl',
        };
    }
}
