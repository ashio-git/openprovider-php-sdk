<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Generate;

use Ashio\OpenProvider\Exception\ErrorCatalog;
use Ashio\OpenProvider\Exception\ErrorCategory;
use Ashio\OpenProvider\Model\ModelMap;
use Ashio\OpenProvider\OpenProvider;
use Ashio\OpenProvider\Service\Operations;
use Ashio\OpenProvider\Spec\Operation;
use Ashio\OpenProvider\Tools\Coverage\CoverageCommand;
use Ashio\OpenProvider\Tools\Spec\Json;
use Ashio\OpenProvider\Tools\Spec\SpecException;

/**
 * What the README states as a number, read from the code users call and from the gates'
 * configuration, never typed by hand (plan S8): the facade's services and each method's
 * #[Operation], the operation registry, the model map, the error catalog, the spec pin, the PHP
 * versions CI tests, and the coverage, mutation and Psalm thresholds the gates enforce.
 */
final class ReadmeInventory
{
    /**
     * @param list<array{accessor: string, group: string, operations: list<array{method: string, http: string, path: string, summary: string, experimental: bool, paged: bool, maxLimit: int|null}>}> $services
     * @param array<string, int>                                                                                                                                                                        $errorCategories ErrorCategory value => codes
     * @param non-empty-list<string>                                                                                                                                                                    $phpVersions     the versions CI tests, oldest first
     */
    public function __construct(
        public readonly array $services,
        public readonly int $specOperations,
        public readonly int $models,
        public readonly int $enums,
        public readonly int $errorCodes,
        public readonly array $errorCategories,
        public readonly string $specCommit,
        public readonly string $specRetrieved,
        public readonly array $phpVersions,
        public readonly float $minCoverage,
        public readonly float $minMsi,
        public readonly int $psalmLevel,
        public readonly string $phpstanLevel,
    ) {
    }

    public static function fromCode(string $root): self
    {
        $spec = Json::object(Json::decode(self::read($root . '/spec/openprovider.json'), 'spec/openprovider.json'), 'spec/openprovider.json');
        $groupOf = [];
        foreach (Json::objectList($spec['x-tagGroups'] ?? null, 'x-tagGroups') as $group) {
            $name = Json::string($group['name'] ?? null, 'x-tagGroups name');
            $groupOf[OperationGenerator::serviceClass($name)] = $name;
        }
        $documented = [];
        foreach (Json::objectMap($spec['paths'] ?? null, 'paths') as $item) {
            foreach (array_keys($item) as $method) {
                if (is_string($method) && !str_starts_with($method, 'x-') && $method !== 'parameters') {
                    $operation = Json::object($item[$method], $method);
                    $documented[Json::string($operation['operationId'] ?? null, 'operationId')] = $operation;
                }
            }
        }

        $services = [];
        foreach ((new \ReflectionClass(OpenProvider::class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $accessor) {
            $type = $accessor->getReturnType();
            $name = $type instanceof \ReflectionNamedType ? $type->getName() : '';
            if (!str_starts_with($name, 'Ashio\\OpenProvider\\Service\\')) {
                continue;
            }
            if (!class_exists($name)) {
                throw new SpecException(sprintf('OpenProvider::%s() returns %s, which does not exist', $accessor->getName(), $name));
            }
            $class = new \ReflectionClass($name);
            $operations = [];
            foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $attribute = $method->getAttributes(Operation::class)[0] ?? null;
                if ($attribute === null) {
                    continue;
                }
                $id = $attribute->newInstance()->operationId;
                $registry = Operations::get($id);
                $operation = $documented[$id] ?? throw new SpecException(sprintf('%s::%s() implements %s, which the spec does not have', $class->getShortName(), $method->getName(), $id));
                $operations[] = [
                    'method' => $method->getName(),
                    'http' => $registry->method,
                    'path' => $registry->path,
                    'summary' => Json::optionalString($operation, 'summary') ?? '',
                    'experimental' => ($operation['x-experimental'] ?? false) === true,
                    'paged' => $registry->paged,
                    'maxLimit' => $registry->maxLimit,
                ];
            }
            $services[] = [
                'accessor' => $accessor->getName(),
                'group' => $groupOf[$class->getShortName()] ?? throw new SpecException(sprintf('%s holds no x-tagGroups group', $class->getShortName())),
                'operations' => $operations,
            ];
        }

        $errorCategories = self::errorCategories((new \ReflectionClassConstant(ErrorCatalog::class, 'CATEGORIES'))->getValue(), ErrorCatalog::count());

        $sources = Json::object(Json::decode(self::read($root . '/spec/sources.json'), 'spec/sources.json'), 'spec/sources.json');
        $repository = Json::object($sources['services'] ?? null, 'sources services');

        return new self(
            $services,
            count($documented),
            count(ModelMap::MODELS),
            count(ModelMap::ENUMS),
            ErrorCatalog::count(),
            $errorCategories,
            Json::string($repository['commit'] ?? null, 'sources services.commit'),
            Json::string($sources['retrieved'] ?? null, 'sources retrieved'),
            self::phpVersions(self::read($root . '/.github/workflows/ci.yml')),
            CoverageCommand::MINIMUM,
            self::minMsi(self::read($root . '/infection.json5')),
            self::psalmLevel(self::read($root . '/psalm.xml')),
            self::phpstanLevel(self::read($root . '/phpstan.neon')),
        );
    }

    /**
     * The `php: [...]` list of the CI test matrix.
     *
     * @return non-empty-list<string>
     */
    public static function phpVersions(string $workflow): array
    {
        if (preg_match("/^\\s*php:\\s*\\[([^\\]]+)\\]/m", $workflow, $match) !== 1) {
            throw new SpecException('ci.yml has no `php: [...]` matrix');
        }
        $versions = array_map(static fn (string $v): string => trim($v, " '\""), explode(',', $match[1]));
        if (array_filter($versions, static fn (string $v): bool => preg_match('/^\d+\.\d+$/', $v) !== 1) !== []) {
            throw new SpecException('ci.yml: the php matrix holds something other than versions');
        }
        usort($versions, static fn (string $a, string $b): int => version_compare($a, $b));

        return $versions;
    }

    /**
     * How many of the catalog's codes fall in each category; a code the catalog does not
     * classify is Other.
     *
     * @return array<string, int> ErrorCategory value => codes
     */
    public static function errorCategories(mixed $classified, int $total): array
    {
        if (!is_array($classified)) {
            throw new SpecException('ErrorCatalog::CATEGORIES is not a map of codes');
        }
        $counts = array_fill_keys(array_map(static fn (ErrorCategory $c): string => $c->value, ErrorCategory::cases()), 0);
        foreach (array_keys($classified) as $code) {
            if (!$classified[$code] instanceof ErrorCategory) {
                throw new SpecException(sprintf('ErrorCatalog::CATEGORIES classifies %s as something other than an ErrorCategory', $code));
            }
            ++$counts[$classified[$code]->value];
        }
        $counts[ErrorCategory::Other->value] += $total - array_sum($counts);

        return $counts;
    }

    public static function minMsi(string $infection): float
    {
        if (preg_match('/^\s*"minMsi"\s*:\s*(\d+(?:\.\d+)?)/m', $infection, $match) !== 1) {
            throw new SpecException('infection.json5 sets no minMsi');
        }

        return (float) $match[1];
    }

    public static function psalmLevel(string $psalm): int
    {
        if (preg_match('/\berrorLevel="(\d)"/', $psalm, $match) !== 1) {
            throw new SpecException('psalm.xml sets no errorLevel');
        }

        return (int) $match[1];
    }

    public static function phpstanLevel(string $neon): string
    {
        if (preg_match('/^\s*level:\s*(max|\d+)\s*$/m', $neon, $match) !== 1) {
            throw new SpecException('phpstan.neon sets no level');
        }

        return $match[1];
    }

    public function operationCount(): int
    {
        return array_sum(array_map(static fn (array $s): int => count($s['operations']), $this->services));
    }

    private static function read(string $file): string
    {
        $contents = @file_get_contents($file);

        return $contents === false ? throw new SpecException('Cannot read ' . $file) : $contents;
    }
}
