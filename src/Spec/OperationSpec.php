<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Spec;

use Ashio\OpenProvider\Exception\InvalidArgumentException;

/**
 * One API operation: method, path relative to the base path, and its parameters. A paged
 * listing takes `limit` and `offset` and returns `results` and `total`; $maxLimit is the largest
 * `limit` the endpoint documents (spec/behavior.json), null when it documents none.
 */
final class OperationSpec
{
    private const METHODS = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

    /** @var array<string, ParameterSpec> */
    private readonly array $byName;

    /**
     * @param list<ParameterSpec> $parameters
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly string $operationId,
        public readonly array $parameters = [],
        public readonly bool $requiresAuth = true,
        public readonly bool $paged = false,
        public readonly ?int $maxLimit = null,
    ) {
        if (!in_array($method, self::METHODS, true)) {
            throw new InvalidArgumentException(sprintf('%s: unsupported method "%s"', $operationId, $method));
        }
        if (!str_starts_with($path, '/')) {
            throw new InvalidArgumentException(sprintf('%s: path "%s" must start with "/"', $operationId, $path));
        }
        $byName = [];
        $bodies = 0;
        foreach ($parameters as $parameter) {
            if (isset($byName[$parameter->name])) {
                throw new InvalidArgumentException(sprintf('%s: parameter "%s" is declared twice', $operationId, $parameter->name));
            }
            $byName[$parameter->name] = $parameter;
            $bodies += $parameter->in === ParameterLocation::Body ? 1 : 0;
        }
        if ($bodies > 1) {
            throw new InvalidArgumentException(sprintf('%s: an operation has at most one body parameter', $operationId));
        }
        if ($paged && (($byName['limit'] ?? null)?->in !== ParameterLocation::Query || ($byName['offset'] ?? null)?->in !== ParameterLocation::Query)) {
            throw new InvalidArgumentException(sprintf('%s: a paged listing needs the query parameters limit and offset', $operationId));
        }
        if ($maxLimit !== null && (!$paged || $maxLimit < 1)) {
            throw new InvalidArgumentException(sprintf('%s: a maximum limit needs a paged listing and must be at least 1', $operationId));
        }
        preg_match_all('/\{([^}]+)\}/', $path, $placeholders);
        foreach ($placeholders[1] as $placeholder) {
            if (($byName[$placeholder] ?? null)?->in !== ParameterLocation::Path) {
                throw new InvalidArgumentException(sprintf('%s: {%s} in %s has no path parameter', $operationId, $placeholder, $path));
            }
        }
        foreach ($byName as $name => $parameter) {
            if ($parameter->in === ParameterLocation::Path && !in_array($name, $placeholders[1], true)) {
                throw new InvalidArgumentException(sprintf('%s: path parameter "%s" does not appear in %s', $operationId, $name, $path));
            }
        }
        $this->byName = $byName;
    }

    /**
     * @param array<array-key, mixed> $operation a Swagger 2.0 operation object
     */
    public static function fromSwagger(string $method, string $path, array $operation): self
    {
        $operationId = isset($operation['operationId']) && is_string($operation['operationId'])
            ? $operation['operationId']
            : strtoupper($method) . ' ' . $path;
        $parameters = [];
        foreach (is_array($operation['parameters'] ?? null) ? $operation['parameters'] : [] as $parameter) {
            if (!is_array($parameter)) {
                throw new InvalidArgumentException($operationId . ': a parameter must be an object');
            }
            $parameters[] = ParameterSpec::fromSwagger($parameter, $operationId);
        }
        $public = array_key_exists('security', $operation) && $operation['security'] === [];

        return new self(strtoupper($method), $path, $operationId, $parameters, !$public);
    }

    public function parameter(string $name): ?ParameterSpec
    {
        return $this->byName[$name] ?? null;
    }

    public function isWrite(): bool
    {
        return $this->method !== 'GET';
    }
}
