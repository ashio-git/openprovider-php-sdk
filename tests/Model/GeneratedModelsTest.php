<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Model;

use Ashio\OpenProvider\Model\ModelMap;
use Ashio\OpenProvider\Serialization\Model;
use Ashio\OpenProvider\Tests\Support\ArrayPath;
use Ashio\OpenProvider\Tools\Generate\ModelGenerator;
use Ashio\OpenProvider\Tools\Generate\NameMap;
use PHPUnit\Framework\TestCase;

/**
 * F4 gates on the committed models: regenerating produces no diff, and every definition reachable
 * from an operation is generated.
 */
final class GeneratedModelsTest extends TestCase
{
    private const ROOT = __DIR__ . '/../..';
    private const ENVELOPE_FIELDS = ['code', 'desc', 'data', 'warnings', 'maintenance'];

    public function testRegeneratingProducesNoDiff(): void
    {
        $expected = ModelGenerator::generate((string) file_get_contents(self::ROOT . '/spec/openprovider.json'));
        $committed = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::ROOT . '/src/Model', \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            self::assertInstanceOf(\SplFileInfo::class, $file);
            $committed[substr($file->getPathname(), strlen(self::ROOT . '/src/Model/'))] = (string) file_get_contents($file->getPathname());
        }
        ksort($committed);

        self::assertSame(array_keys($expected), array_keys($committed), 'src/Model holds exactly the generated files');
        foreach ($expected as $path => $source) {
            self::assertSame($source, $committed[$path], $path);
        }
    }

    /**
     * Checked from the spec, not from the generator's own walk: the roots are generated, the
     * generated set is closed under $ref, and what is left out is only what the core reads.
     */
    public function testEveryDefinitionReachableFromAnOperationIsGenerated(): void
    {
        $spec = self::spec();
        $definitions = ArrayPath::entries($spec, 'definitions');
        $generated = ModelMap::MODELS + ModelMap::ENUMS;
        self::assertSame([], array_intersect_key(ModelMap::MODELS, ModelMap::ENUMS));

        $envelopes = [];
        $errors = [];
        foreach (ArrayPath::entries($spec, 'paths') as $path => $operations) {
            foreach (ArrayPath::entries($operations) as $method => $operation) {
                if (ArrayPath::at($operation, 'operationId') === null) {
                    continue;
                }
                $where = strtoupper((string) $method) . ' ' . $path;
                foreach (ArrayPath::entries($operation, 'parameters') as $parameter) {
                    foreach (self::refs($parameter) as $ref) {
                        self::assertArrayHasKey($ref, ModelMap::MODELS, "$where: body $ref");
                    }
                }
                $envelope = self::refName(ArrayPath::at($operation, 'responses', '200', 'schema', '$ref'));
                $envelopes[$envelope] = true;
                $fields = array_keys(ArrayPath::entries($definitions, $envelope, 'properties'));
                self::assertSame([], array_diff($fields, self::ENVELOPE_FIELDS), "$where: $envelope has a field ApiResponse does not read");
                foreach (self::refs(ArrayPath::at($definitions, $envelope, 'properties', 'data')) as $ref) {
                    self::assertArrayHasKey($ref, $generated, "$where: data $ref");
                }
                $error = ArrayPath::at($operation, 'responses', 'default', 'schema', '$ref');
                if ($error !== null) {
                    $errors[self::refName($error)] = true;
                }
            }
        }

        foreach (array_keys($generated) as $name) {
            foreach (self::refs($definitions[$name] ?? null) as $ref) {
                self::assertArrayHasKey($ref, $generated, "$name refers to $ref");
            }
        }

        $readByTheCore = [];
        foreach (array_keys($envelopes + $errors) as $name) {
            $readByTheCore[$name] = true;
            foreach (self::refs(ArrayPath::at($definitions, $name, 'properties', 'warnings')) as $ref) {
                $readByTheCore[$ref] = true;
            }
        }
        foreach (array_keys(array_diff_key($definitions, $generated)) as $name) {
            self::assertArrayHasKey($name, $readByTheCore, "$name is neither generated nor read by the core");
        }
    }

    public function testEveryModelHasTheGeneratedShapeAndEveryEnumTheSpecValues(): void
    {
        $definitions = ArrayPath::entries(self::spec(), 'definitions');
        foreach (ModelMap::MODELS as $definition => $class) {
            $reflection = new \ReflectionClass($class);
            self::assertTrue($reflection->isFinal() && $reflection->isReadOnly(), $class);
            self::assertTrue($reflection->implementsInterface(Model::class), $class);
            self::assertStringContainsString("Spec definition `$definition`.", (string) $reflection->getDocComment());
            self::assertStringContainsString('@generated', (string) $reflection->getDocComment());
            self::assertSame(
                ArrayPath::at($definitions, $definition, 'x-experimental') === true,
                str_contains((string) $reflection->getDocComment(), '@experimental'),
                "$class: @experimental follows x-experimental",
            );

            $keys = array_map('strval', array_keys(ArrayPath::entries($definitions, $definition, 'properties')));
            $parameters = $reflection->getConstructor()?->getParameters() ?? [];
            self::assertSame(
                [...array_values(NameMap::properties($definition, $keys)), 'extra'],
                array_map(static fn (\ReflectionParameter $p): string => $p->getName(), $parameters),
                $class,
            );
            foreach ($parameters as $parameter) {
                self::assertTrue($parameter->isPromoted() && $parameter->isOptional(), "$class::\${$parameter->getName()}");
            }
        }

        foreach (ModelMap::ENUMS as $definition => $class) {
            $constant = new \ReflectionClassConstant($class, 'VALUES');
            self::assertSame(ArrayPath::at($definitions, $definition, 'enum'), $constant->getValue(), $class);
        }
    }

    private static function spec(): mixed
    {
        return json_decode((string) file_get_contents(self::ROOT . '/spec/openprovider.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return list<string> the definitions a schema refers to, at any depth
     */
    private static function refs(mixed $schema): array
    {
        if (!is_array($schema)) {
            return [];
        }
        $refs = is_string($schema['$ref'] ?? null) ? [self::refName($schema['$ref'])] : [];
        foreach ($schema as $value) {
            array_push($refs, ...self::refs($value));
        }

        return $refs;
    }

    private static function refName(mixed $ref): string
    {
        self::assertIsString($ref);
        self::assertStringStartsWith('#/definitions/', $ref);

        return substr($ref, strlen('#/definitions/'));
    }
}
