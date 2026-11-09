<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Support;

use Ashio\OpenProvider\Client\AbstractService;
use Ashio\OpenProvider\Spec\Operation;

/**
 * The operations of spec/openprovider.json read straight from the JSON, and the methods that
 * carry an #[Operation], found by reflection: the two sides the registry and contract tests
 * compare, neither of them taken from the generator.
 */
final class SpecOperations
{
    public const ROOT = __DIR__ . '/../..';
    public const SPEC = self::ROOT . '/spec/openprovider.json';
    private const HTTP_METHODS = ['get', 'post', 'put', 'delete', 'patch'];

    /** @var array<array-key, mixed>|null */
    private static ?array $spec = null;

    /**
     * @return array<array-key, mixed>
     */
    public static function spec(): array
    {
        if (self::$spec === null) {
            $spec = json_decode((string) file_get_contents(self::SPEC), true, 512, JSON_THROW_ON_ERROR);
            self::$spec = is_array($spec) ? $spec : throw new \UnexpectedValueException(self::SPEC . ' is not an object');
        }

        return self::$spec;
    }

    /**
     * @return array<array-key, mixed>
     */
    public static function definitions(): array
    {
        return ArrayPath::entries(self::spec(), 'definitions');
    }

    /**
     * Every operation, keyed by operationId, in spec order.
     *
     * @return array<string, array{method: string, path: string, group: string, operation: array<array-key, mixed>}>
     */
    public static function all(): array
    {
        $groupOf = [];
        foreach (ArrayPath::entries(self::spec(), 'x-tagGroups') as $group) {
            $group = ArrayPath::entries($group);
            foreach (ArrayPath::entries($group, 'tags') as $tag) {
                $groupOf[self::string($tag)] = self::string(ArrayPath::at($group, 'name'));
            }
        }
        $all = [];
        foreach (ArrayPath::entries(self::spec(), 'paths') as $path => $item) {
            foreach (ArrayPath::entries($item) as $method => $operation) {
                if (!in_array($method, self::HTTP_METHODS, true)) {
                    continue;
                }
                $operation = ArrayPath::entries($operation);
                $tags = ArrayPath::entries($operation, 'tags');
                $all[self::string(ArrayPath::at($operation, 'operationId'))] = [
                    'method' => strtoupper((string) $method),
                    'path' => (string) $path,
                    'group' => $groupOf[self::string($tags[0] ?? '')] ?? '',
                    'operation' => $operation,
                ];
            }
        }

        return $all;
    }

    /**
     * The service classes: every class in src/Service that extends AbstractService.
     *
     * @return list<class-string<AbstractService>>
     */
    public static function services(): array
    {
        $services = [];
        foreach (glob(self::ROOT . '/src/Service/*.php') ?: [] as $file) {
            $class = 'Ashio\\OpenProvider\\Service\\' . basename($file, '.php');
            if (class_exists($class) && is_subclass_of($class, AbstractService::class)) {
                $services[] = $class;
            }
        }
        sort($services);

        return $services;
    }

    /**
     * Each #[Operation] method: operationId => list of [class, method, attribute].
     *
     * @return array<string, list<array{class-string<AbstractService>, string, Operation}>>
     */
    public static function methods(): array
    {
        $methods = [];
        foreach (self::services() as $class) {
            foreach ((new \ReflectionClass($class))->getMethods() as $method) {
                foreach ($method->getAttributes(Operation::class) as $attribute) {
                    $operation = $attribute->newInstance();
                    $methods[$operation->operationId][] = [$class, $method->getName(), $operation];
                }
            }
        }

        return $methods;
    }

    /**
     * The documented naming rule for PHP names (AGENTS.md), written here independently of the
     * generator: camelCase of the API name's runs of letters and digits, a run in capitals
     * lowered first (`domain.name` → `domainName`, `AUD` → `aud`).
     */
    public static function camel(string $name): string
    {
        $out = '';
        foreach (preg_split('/[^A-Za-z0-9]+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $i => $word) {
            $word = strtoupper($word) === $word ? strtolower($word) : $word;
            $out .= $i === 0 ? lcfirst($word) : ucfirst($word);
        }

        return $out;
    }

    private static function string(mixed $value): string
    {
        return is_string($value) ? $value : throw new \UnexpectedValueException('Expected a string, got ' . get_debug_type($value));
    }

    /**
     * The definition a `{"$ref": "#/definitions/x"}` schema names.
     *
     * @param array<array-key, mixed> $schema
     */
    public static function refName(array $schema): string
    {
        $ref = $schema['$ref'] ?? null;
        if (!is_string($ref) || !str_starts_with($ref, '#/definitions/')) {
            throw new \UnexpectedValueException('Not a $ref: ' . json_encode($schema));
        }

        return substr($ref, strlen('#/definitions/'));
    }
}
