<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Spec;

/**
 * Where a parameter travels. The merged spec uses only these three (formData and header do
 * not occur), so the encoder supports exactly them.
 */
enum ParameterLocation: string
{
    case Path = 'path';
    case Query = 'query';
    case Body = 'body';
}
