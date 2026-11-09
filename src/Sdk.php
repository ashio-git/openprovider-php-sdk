<?php

declare(strict_types=1);

namespace Ashio\OpenProvider;

final class Sdk
{
    public const VERSION = '3.0.0-dev';

    public static function userAgent(): string
    {
        return 'ashio-openprovider-php/' . self::VERSION;
    }
}
