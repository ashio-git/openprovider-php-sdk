<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Spec;

/**
 * Reads the spec out of the developer portal's `swagger.spec.js` without executing it.
 *
 * The file is a single statement, `window.OP_API_SPEC = {…};`. Anything else is refused,
 * because the content is untrusted data, not code.
 */
final class PortalBundle
{
    public static function extract(string $script, string $variable): string
    {
        $prefix = $variable . ' = ';
        $body = rtrim($script);
        if (!str_starts_with($body, $prefix)) {
            throw new SpecException(sprintf('The portal bundle does not start with "%s"', $prefix));
        }

        $json = substr($body, strlen($prefix));
        if (str_ends_with($json, ';')) {
            $json = substr($json, 0, -1);
        }
        if (!str_starts_with($json, '{') || !str_ends_with($json, '}')) {
            throw new SpecException(sprintf('The value assigned to %s is not a single JSON object', $variable));
        }

        return $json;
    }
}
