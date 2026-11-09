<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Service;

use Ashio\OpenProvider\Auth\Credentials;
use Ashio\OpenProvider\Client\AbstractService;
use Ashio\OpenProvider\Client\ApiClient;
use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\OpenProvider;
use Ashio\OpenProvider\Service\Operations;
use Ashio\OpenProvider\Spec\Operation;
use Ashio\OpenProvider\Spec\OperationSpec;
use Ashio\OpenProvider\Tests\Support\FakeHttpClient;
use Ashio\OpenProvider\Tests\Support\SpecOperations;
use Ashio\OpenProvider\Tools\Generate\OperationGenerator;
use PHPUnit\Framework\TestCase;

/**
 * S1: every operation of spec/openprovider.json maps to exactly one method with its
 * #[Operation], and no public method of a service is anything else. No tag group is deferred
 * (OperationGenerator::DEFERRED_GROUPS), so none of the 140 may be missing.
 */
final class OperationRegistryTest extends TestCase
{
    public function testEveryOperationHasExactlyOneMethod(): void
    {
        $methods = SpecOperations::methods();
        foreach (SpecOperations::all() as $id => $operation) {
            self::assertArrayHasKey($id, $methods, "$id has no method");
            self::assertCount(1, $methods[$id], "$id has more than one method");
            [$class, $method, $attribute] = $methods[$id][0];
            self::assertSame([$operation['method'], $operation['path']], [$attribute->method, $attribute->path], "$class::$method()");
        }
        self::assertSame([], array_keys(array_diff_key($methods, SpecOperations::all())), 'methods for operations that are not in the spec');
    }

    public function testEveryPublicMethodOfAServiceIsAnOperation(): void
    {
        self::assertNotSame([], SpecOperations::services());
        foreach (SpecOperations::services() as $class) {
            foreach ((new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() === AbstractService::class) {
                    continue;
                }
                self::assertCount(1, $method->getAttributes(Operation::class), sprintf('%s::%s() is public but implements no operation', $class, $method->getName()));
            }
        }
    }

    public function testEachServiceHoldsOneTagGroup(): void
    {
        $groupsOf = [];
        foreach (SpecOperations::methods() as $id => [[$class]]) {
            $groupsOf[$class][SpecOperations::all()[$id]['group']] = true;
        }
        foreach ($groupsOf as $class => $groups) {
            self::assertCount(1, $groups, "$class mixes the groups " . implode(', ', array_keys($groups)));
            self::assertSame(OperationGenerator::serviceClass((string) array_key_first($groups)), substr($class, strrpos($class, '\\') + 1));
        }
    }

    public function testNoGroupIsDeferredAndAllOperationsAreImplemented(): void
    {
        self::assertSame([], OperationGenerator::DEFERRED_GROUPS);
        self::assertCount(140, SpecOperations::all());
        self::assertCount(140, SpecOperations::methods());
        self::assertCount(28, array_filter(SpecOperations::all(), static fn (array $o): bool => $o['group'] === 'Mailcow'));
    }

    /**
     * Plan §4.2: what only the developer portal documents (Mailcow's white-label and the reseller
     * signup) says @experimental, and nothing else does.
     */
    public function testExactlyThePortalOnlyOperationsSayExperimental(): void
    {
        $methods = SpecOperations::methods();
        $experimental = [];
        $documented = [];
        foreach (SpecOperations::all() as $id => $operation) {
            if (($operation['operation']['x-experimental'] ?? false) === true) {
                $experimental[] = $id;
            }
            self::assertArrayHasKey($id, $methods, "$id has no method");
            [[$class, $method]] = $methods[$id];
            if (str_contains((string) (new \ReflectionMethod($class, $method))->getDocComment(), '@experimental')) {
                $documented[] = $id;
            }
        }

        self::assertSame([
            'RetrieveWhiteLabelDomain',
            'UpsertWhiteLabelDomain',
            'RetrieveWhiteLabelLogo',
            'ModifyWhiteLabelLogo',
            'CreateResellerPublic',
        ], $experimental);
        self::assertSame($experimental, $documented);
    }

    public function testTheRegistryEncodesEachOperationAsTheSpecDeclaresIt(): void
    {
        self::assertSame(array_keys(SpecOperations::all()), Operations::IDS);
        foreach (SpecOperations::all() as $id => $operation) {
            $expected = OperationSpec::fromSwagger($operation['method'], $operation['path'], $operation['operation']);
            if ($id === 'Login') {
                // spec/behavior.json: the Swagger files do not mark the login public, but it is.
                $expected = new OperationSpec($expected->method, $expected->path, $id, $expected->parameters, false);
            }
            self::assertEquals($expected, Operations::get($id), $id);
        }
        self::assertFalse(Operations::get('CreateResellerPublic')->requiresAuth);
        self::assertTrue(Operations::get('GetReseller')->requiresAuth);
    }

    public function testTheFacadeReachesEveryServiceOnce(): void
    {
        $returned = [];
        foreach ((new \ReflectionClass(OpenProvider::class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (in_array($method->getName(), ['__construct', 'client'], true)) {
                continue;
            }
            $type = $method->getReturnType();
            self::assertInstanceOf(\ReflectionNamedType::class, $type);
            $returned[] = $type->getName();
        }
        sort($returned);
        self::assertSame(SpecOperations::services(), $returned);

        $client = ApiClient::create(ClientConfig::sandbox(), new Credentials('reseller', 'pw'), null, new FakeHttpClient());
        $openProvider = new OpenProvider($client);
        self::assertSame($client, $openProvider->client());
        self::assertSame($openProvider->dns(), $openProvider->dns());
    }

    public function testRegeneratingProducesNoDiff(): void
    {
        $files = OperationGenerator::generate(
            (string) file_get_contents(SpecOperations::SPEC),
            (string) file_get_contents(SpecOperations::ROOT . '/spec/behavior.json'),
        );
        $onDisk = array_map(
            static fn (string $file): string => 'Service/' . basename($file),
            glob(SpecOperations::ROOT . '/src/Service/*') ?: [],
        );
        self::assertSame(
            array_values(array_filter(array_keys($files), static fn (string $path): bool => str_starts_with($path, 'Service/'))),
            $onDisk,
            'src/Service holds generated files only',
        );
        foreach ($files as $path => $source) {
            self::assertSame($source, file_get_contents(SpecOperations::ROOT . '/src/' . $path), "src/$path is out of date: run php tools/generate operations");
        }
    }
}
