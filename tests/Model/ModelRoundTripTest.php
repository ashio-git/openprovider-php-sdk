<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Model;

use Ashio\OpenProvider\Model\ModelMap;
use Ashio\OpenProvider\Serialization\Model;
use Ashio\OpenProvider\Tests\Support\SpecPayloads;
use Ashio\OpenProvider\Tools\Generate\NameMap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * F4 gate (S3): every generated model survives fromArray → toArray, and fromArray → JSON, with
 * nothing lost or changed, over 200 payloads built from its schema (fixed seed per model, so a
 * failure replays) plus the edge cases 0, 0.0, false, '', empty lists and objects, explicit nulls,
 * values of another type and unknown keys. Values of the declared type must land in the typed
 * properties, not in $extra.
 */
final class ModelRoundTripTest extends TestCase
{
    private const PAYLOADS = 200;
    private const SEED = 20261109;
    private const JSON_FLAGS = JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;

    /** @var array<string, array<string, mixed>>|null */
    private static ?array $definitions = null;

    /**
     * @return iterable<string, array{string, class-string<Model>}>
     */
    public static function models(): iterable
    {
        foreach (ModelMap::MODELS as $definition => $class) {
            if (!is_a($class, Model::class, true)) {
                throw new \UnexpectedValueException("$class is not a Model");
            }
            yield $definition => [$definition, $class];
        }
    }

    /**
     * @param class-string<Model> $class
     */
    #[DataProvider('models')]
    public function testRandomPayloadsSurviveTheRoundTrip(string $definition, string $class): void
    {
        $payloads = new SpecPayloads(self::definitions(), new Randomizer(new Mt19937(self::SEED ^ crc32($definition))));
        for ($i = 0; $i < self::PAYLOADS; ++$i) {
            $this->assertRoundTrip($definition, $class, $payloads->object($definition), "payload $i");
        }
    }

    /**
     * @param class-string<Model> $class
     */
    #[DataProvider('models')]
    public function testEdgeCasesSurviveTheRoundTrip(string $definition, string $class): void
    {
        foreach (SpecPayloads::edgeCases(self::definitions(), $definition) as $case => $payload) {
            $this->assertRoundTrip($definition, $class, $payload, $case);
        }
    }

    /**
     * The control: the comparison used above tells apart what a lossy round trip would confuse.
     */
    public function testTheComparisonDetectsEveryLossTheGateIsAbout(): void
    {
        self::assertNotSame(self::canonical(['a' => 0]), self::canonical(['a' => false]));
        self::assertNotSame(self::canonical(['a' => 0]), self::canonical(['a' => 0.0]));
        self::assertNotSame(self::canonical(['a' => 0]), self::canonical(['a' => '0']));
        self::assertNotSame(self::canonical(['a' => '']), self::canonical([]));
        self::assertNotSame(self::canonical(['a' => null]), self::canonical([]));
        self::assertNotSame(self::canonical(['a' => []]), self::canonical([]));
        self::assertNotSame(self::canonical([1, 2]), self::canonical([2, 1]));
        self::assertSame(self::canonical(['b' => 1, 'a' => ['y' => 2, 'x' => 1]]), self::canonical(['a' => ['x' => 1, 'y' => 2], 'b' => 1]));
    }

    public function testThereIsAModelPerObjectDefinitionToTest(): void
    {
        self::assertGreaterThan(300, count(ModelMap::MODELS));
    }

    /**
     * @param class-string<Model>     $class
     * @param array<array-key, mixed> $payload
     */
    private function assertRoundTrip(string $definition, string $class, array $payload, string $case): void
    {
        $label = "$definition, $case: " . json_encode($payload, self::JSON_FLAGS);
        $model = $class::fromArray($payload);

        self::assertSame(self::canonical($payload), self::canonical($model->toArray()), "$label\ntoArray() differs");
        self::assertSame(
            self::canonical($payload),
            self::canonical(json_decode(json_encode($model, self::JSON_FLAGS), true, 512, JSON_THROW_ON_ERROR)),
            "$label\nJSON differs",
        );

        $definitions = self::definitions();
        $properties = SpecPayloads::properties($definitions, $definition);
        $names = NameMap::properties($definition, array_map('strval', array_keys($properties)));
        $extra = $payload;
        foreach ($properties as $key => $schema) {
            $value = (new \ReflectionProperty($model, $names[$key]))->getValue($model);
            $typed = array_key_exists($key, $payload) && $payload[$key] !== null && SpecPayloads::fits($definitions, $schema, $payload[$key]);
            if (!$typed) {
                self::assertNull($value, "$label\n$key must stay in extra");

                continue;
            }
            unset($extra[$key]);
            self::assertNotNull($value, "$label\n$key must be typed");
            $target = self::modelClass($schema['$ref'] ?? null);
            $itemTarget = self::modelClass(is_array($schema['items'] ?? null) ? ($schema['items']['$ref'] ?? null) : null);
            if ($target !== null) {
                self::assertInstanceOf($target, $value, "$label\n$key");
            } elseif ($itemTarget !== null) {
                self::assertIsArray($value);
                self::assertContainsOnlyInstancesOf($itemTarget, $value, "$label\n$key");
            } else {
                self::assertSame($payload[$key], $value, "$label\n$key");
            }
        }
        self::assertSame(self::canonical($extra), self::canonical((new \ReflectionProperty($model, 'extra'))->getValue($model)), "$label\nextra differs");
    }

    /**
     * @return class-string<Model>|null
     */
    private static function modelClass(mixed $ref): ?string
    {
        $class = is_string($ref) ? (ModelMap::MODELS[substr($ref, strlen('#/definitions/'))] ?? null) : null;

        return $class !== null && is_a($class, Model::class, true) ? $class : null;
    }

    /**
     * Maps compare without regard to key order; lists, types and values compare strictly.
     */
    private static function canonical(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        if (!array_is_list($value)) {
            ksort($value, SORT_STRING);
        }

        return array_map(self::canonical(...), $value);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function definitions(): array
    {
        return self::$definitions ??= SpecPayloads::definitions(__DIR__ . '/../../spec/openprovider.json');
    }
}
