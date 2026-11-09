<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Spec;

use Ashio\OpenProvider\Tools\Spec\Json;
use Ashio\OpenProvider\Tools\Spec\MergeResult;
use Ashio\OpenProvider\Tools\Spec\Overrides;
use Ashio\OpenProvider\Tools\Spec\SpecException;
use Ashio\OpenProvider\Tools\Spec\SpecMerger;
use PHPUnit\Framework\TestCase;

/**
 * The merge policy of spec/PROVENANCE.md on small hand-written inputs.
 */
final class SpecMergerTest extends TestCase
{
    public function testTheOperationSetIsExactlyThePortalSet(): void
    {
        $result = $this->merge();
        $spec = $result->spec();

        self::assertSame(
            ['GET /domains', 'GET /mailcow/orders/{id}', 'POST /mailcow/orders', 'POST /resellers/signup', 'POST /ssl/orders'],
            self::operationKeys($spec),
        );
        self::assertSame(['GET /notification-setting'], $result->serviceOnlyOperations());
        self::assertSame(['POST /resellers/signup'], $result->portalOnlyOperations());
    }

    public function testPathsDropTheVersionPrefixAndTheBasePathIsV1(): void
    {
        $spec = $this->merge()->spec();

        self::assertSame('/v1', $spec['basePath']);
        self::assertArrayHasKey('/domains', self::paths($spec));
    }

    public function testDefinitionsArePrefixedWithTheServiceSoEqualNamesCannotCollide(): void
    {
        $spec = $this->merge()->spec();
        $definitions = self::definitions($spec);

        self::assertSame(
            ['product_id', 'csr', 'approver_email'],
            array_keys(self::properties($definitions['ssl.orderCreateOrderRequest'])),
        );
        self::assertSame(['period', 'quantity'], array_keys(self::properties($definitions['mailcow.orderCreateOrderRequest'])));
        self::assertSame('#/definitions/ssl.orderCreateOrderRequest', self::bodyRef($spec, '/ssl/orders', 'post'));
        self::assertSame('#/definitions/mailcow.orderCreateOrderRequest', self::bodyRef($spec, '/mailcow/orders', 'post'));
    }

    public function testFieldsOnlyThePortalHasAreAddedAndMarked(): void
    {
        $definitions = self::definitions($this->merge()->spec());
        $domain = self::properties($definitions['domain.domainDomain']);

        self::assertSame(['id', 'name', 'abuse_status', 'legacy_flag'], array_keys($domain));
        self::assertSame(['type' => 'boolean', 'x-openprovider-source' => 'developer-portal'], $domain['legacy_flag']);
    }

    public function testEnumValuesAreUnited(): void
    {
        $definitions = self::definitions($this->merge()->spec());
        $status = self::properties($definitions['domain.domainDomain'])['abuse_status'];

        self::assertIsArray($status);
        self::assertSame(['ACT', 'SUS', 'OLD'], $status['enum']);
    }

    public function testOnlyDefinitionsReachableFromTheOperationsAreEmitted(): void
    {
        $definitions = self::definitions($this->merge()->spec());

        self::assertArrayNotHasKey('domain.domainUnused', $definitions);
        self::assertArrayNotHasKey('domain.contactNotificationSetting', $definitions);
    }

    public function testPortalOnlyOperationsAndTheirDefinitionsAreExperimental(): void
    {
        $spec = $this->merge()->spec();
        $signup = self::operation($spec, '/resellers/signup', 'post');
        $definitions = self::definitions($spec);

        self::assertTrue($signup['x-experimental']);
        self::assertEquals([], $signup['security']);
        self::assertSame('#/definitions/portal.signupCreateResellerRequest', self::bodyRef($spec, '/resellers/signup', 'post'));
        self::assertIsArray($definitions['portal.signupCreateResellerRequest']);
        self::assertTrue($definitions['portal.signupCreateResellerRequest']['x-experimental']);
    }

    public function testParametersAreUnitedAndAnExplicitRequiredFalseIsNotADifference(): void
    {
        $result = $this->merge();
        $parameters = self::operation($result->spec(), '/domains', 'get')['parameters'];

        self::assertIsArray($parameters);
        self::assertSame(['limit', 'domain.name', 'legacy_filter'], array_column($parameters, 'name'));
        self::assertSame([], $result->conflicts());
    }

    public function testATypeConflictWithoutAnOverrideFailsTheMerge(): void
    {
        $result = $this->merge(portalPatches: [[['definitions', 'domainDomain', 'properties', 'id', 'type'], 'string']]);

        self::assertSame(['definitions/domain.domainDomain/properties/id'], array_keys($result->unresolvedConflicts()));
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('definitions/domain.domainDomain/properties/id');
        $result->spec();
    }

    public function testAnOverrideResolvesATypeConflictEitherWay(): void
    {
        $patch = [[['definitions', 'domainDomain', 'properties', 'id', 'type'], 'string']];
        $id = 'definitions/domain.domainDomain/properties/id';

        $portalWins = $this->merge(['conflicts' => [$id => ['use' => 'portal', 'reason' => 'live API returns a string']]], $patch);
        $serviceWins = $this->merge(['conflicts' => [$id => ['use' => 'service', 'reason' => 'newer release']]], $patch);

        $portalId = self::properties(self::definitions($portalWins->spec())['domain.domainDomain'])['id'];
        $serviceId = self::properties(self::definitions($serviceWins->spec())['domain.domainDomain'])['id'];
        self::assertIsArray($portalId);
        self::assertIsArray($serviceId);
        self::assertSame('string', $portalId['type']);
        self::assertSame('integer', $serviceId['type']);
        self::assertSame('live API returns a string', $portalWins->conflicts()[$id]->resolution?->reason);
    }

    public function testAParameterTypeConflictIsReported(): void
    {
        $result = $this->merge(portalPatches: [[['paths', '/v1/domains', 'get', 'parameters', 0, 'type'], 'string']]);

        self::assertSame(['paths/GET /domains/parameters/query:limit'], array_keys($result->unresolvedConflicts()));
    }

    public function testAStaleOverrideFailsTheMerge(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('definitions/domain.domainDomain/properties/nope');

        $this->merge(['conflicts' => ['definitions/domain.domainDomain/properties/nope' => ['use' => 'portal', 'reason' => 'x']]]);
    }

    public function testADuplicateOperationIdFailsUntilAnOverrideRenamesIt(): void
    {
        $duplicate = [[['paths', '/v1/mailcow/orders', 'post', 'operationId'], 'CreateOrder']];

        $unresolved = $this->merge(portalPatches: $duplicate);
        self::assertSame(['operationId/CreateOrder'], array_keys($unresolved->unresolvedConflicts()));

        $renamed = $this->merge(
            ['operationIds' => ['POST /mailcow/orders' => ['operationId' => 'CreateMailcowOrder', 'reason' => 'portal reuses CreateOrder']]],
            $duplicate,
        );
        self::assertSame('CreateMailcowOrder', self::operation($renamed->spec(), '/mailcow/orders', 'post')['operationId']);
        self::assertSame('CreateOrder', self::operation($renamed->spec(), '/ssl/orders', 'post')['operationId']);
        self::assertSame([], $renamed->unresolvedConflicts());
    }

    public function testAnOperationIdOverrideForAnUnknownOperationFails(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('GET /nope');

        $this->merge(['operationIds' => ['GET /nope' => ['operationId' => 'Nope', 'reason' => 'x']]]);
    }

    public function testOverridesNeedAReason(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('reason');

        Overrides::fromArray(['conflicts' => ['x' => ['use' => 'portal', 'reason' => ' ']]]);
    }

    public function testAServiceNamedLikeThePortalNamespaceIsACollision(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('portal');

        (new SpecMerger())->merge(['portal' => self::service('/v1beta/x', [])], self::portal());
    }

    public function testServiceNamesThatCouldCollideAfterPrefixingAreRefused(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('a.b');

        (new SpecMerger())->merge(['a.b' => self::service('/v1beta/x', [])], self::portal());
    }

    public function testTwoServicesDeclaringTheSameOperationIsACollision(): void
    {
        $services = self::services();
        $services['mailcow'] = self::patch($services['mailcow'], ['paths', '/v1beta/domains'], self::dig($services['domain'], ['paths', '/v1beta/domains']));

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('GET /domains');

        (new SpecMerger())->merge($services, self::portal());
    }

    public function testTheSameInputsGiveTheSameBytesWhateverTheServiceOrder(): void
    {
        $services = self::services();
        $reversed = array_reverse($services, true);

        $first = Json::encode((new SpecMerger())->merge($services, self::portal())->spec());
        $second = Json::encode((new SpecMerger())->merge($services, self::portal())->spec());
        $third = Json::encode((new SpecMerger())->merge($reversed, self::portal())->spec());

        self::assertSame($first, $second);
        self::assertSame($first, $third);
    }

    public function testADanglingReferenceIsAnError(): void
    {
        $services = self::services();
        $services['domain'] = self::patch($services['domain'], ['paths', '/v1beta/domains', 'get', 'responses', '200', 'schema', '$ref'], '#/definitions/missing');

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('missing');

        (new SpecMerger())->merge($services, self::portal());
    }

    /**
     * @param array<string, mixed>                         $overrides
     * @param list<array{list<string|int>, mixed}> $portalPatches path into the portal spec => new value
     */
    private function merge(array $overrides = [], array $portalPatches = []): MergeResult
    {
        $portal = self::portal();
        foreach ($portalPatches as [$path, $value]) {
            $portal = self::patch($portal, $path, $value);
        }

        return (new SpecMerger(Overrides::fromArray($overrides)))->merge(self::services(), $portal);
    }

    /**
     * @param array<array-key, mixed> $data
     * @param list<string|int>        $path
     *
     * @return array<array-key, mixed>
     */
    private static function patch(array $data, array $path, mixed $value): array
    {
        $key = array_shift($path);
        self::assertNotNull($key);
        if ($path === []) {
            $data[$key] = $value;

            return $data;
        }
        $child = $data[$key] ?? null;
        self::assertIsArray($child);
        $data[$key] = self::patch($child, $path, $value);

        return $data;
    }

    /**
     * @param array<array-key, mixed> $data
     * @param list<string|int>        $path
     */
    private static function dig(array $data, array $path): mixed
    {
        $value = $data;
        foreach ($path as $key) {
            self::assertIsArray($value);
            $value = $value[$key] ?? null;
        }

        return $value;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function services(): array
    {
        $error = ['type' => 'object', 'properties' => ['code' => ['type' => 'integer', 'format' => 'int32']]];

        return [
            'ssl' => self::service('/v1beta/ssl/orders', [
                'post' => self::op('CreateOrder', 'orderCreateOrderRequest', 'orderCreateOrderResponse'),
            ], [
                'orderCreateOrderRequest' => self::object(['product_id' => ['type' => 'integer'], 'csr' => ['type' => 'string'], 'approver_email' => ['type' => 'string']]),
                'orderCreateOrderResponse' => self::object(['code' => ['type' => 'integer'], 'error' => ['$ref' => '#/definitions/errorError']]),
                'errorError' => $error,
            ]),
            'mailcow' => self::service('/v1beta/mailcow/orders', [
                'post' => self::op('CreateOrder', 'orderCreateOrderRequest', 'orderCreateOrderResponse'),
            ], [
                'orderCreateOrderRequest' => self::object(['period' => ['type' => 'integer'], 'quantity' => ['type' => 'integer']]),
                'orderCreateOrderResponse' => self::object(['code' => ['type' => 'integer'], 'error' => ['$ref' => '#/definitions/errorError']]),
                'orderGetOrderResponse' => self::object(['code' => ['type' => 'integer']]),
                'errorError' => $error,
            ], [
                '/v1beta/mailcow/orders/{id}' => ['get' => self::op('GetOrder', null, 'orderGetOrderResponse', [['name' => 'id', 'in' => 'path', 'required' => true, 'type' => 'integer']])],
            ]),
            'domain' => self::service('/v1beta/domains', [
                'get' => self::op('ListDomains', null, 'domainDomain', [
                    ['name' => 'limit', 'in' => 'query', 'type' => 'integer', 'format' => 'int32'],
                    ['name' => 'domain.name', 'in' => 'query', 'type' => 'string', 'description' => 'new text'],
                ]),
            ], [
                'domainDomain' => self::object([
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'abuse_status' => ['type' => 'string', 'enum' => ['ACT', 'SUS']],
                ]),
                'domainUnused' => self::object(['x' => ['type' => 'string']]),
                'contactNotificationSetting' => self::object(['y' => ['type' => 'string']]),
            ], [
                '/v1beta/notification-setting' => ['get' => self::op('GetNotificationSetting', null, 'contactNotificationSetting')],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function portal(): array
    {
        $error = ['type' => 'object', 'properties' => ['code' => ['type' => 'integer', 'format' => 'int32']]];

        return [
            'swagger' => '2.0',
            'info' => ['title' => 'REST API', 'version' => '1.0.0-beta'],
            'host' => 'api.openprovider.eu',
            'security' => [['Bearer' => []]],
            'paths' => [
                '/v1/domains' => ['get' => self::op('ListDomains', null, 'domainDomain', [
                    ['name' => 'limit', 'in' => 'query', 'required' => false, 'type' => 'integer', 'format' => 'int32'],
                    ['name' => 'domain.name', 'in' => 'query', 'required' => false, 'type' => 'string', 'description' => 'old text'],
                    ['name' => 'legacy_filter', 'in' => 'query', 'type' => 'string'],
                ])],
                '/v1/ssl/orders' => ['post' => self::op('CreateOrder', 'orderCreateOrderRequest', 'orderCreateOrderResponse')],
                '/v1/mailcow/orders' => ['post' => self::op('CreateMailcowOrderPortal', 'mcOrder_orderCreateOrderRequest', 'mcOrder_orderCreateOrderResponse')],
                '/v1/mailcow/orders/{id}' => ['get' => self::op('GetOrder', null, 'mcOrder_orderGetOrderResponse', [['name' => 'id', 'in' => 'path', 'required' => true, 'type' => 'integer']])],
                '/v1/resellers/signup' => ['post' => self::op('CreateResellerPublic', 'signupCreateResellerRequest', 'signupCreateResellerResponse') + ['security' => []]],
            ],
            'definitions' => [
                'domainDomain' => self::object([
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'abuse_status' => ['type' => 'string', 'enum' => ['ACT', 'OLD']],
                    'legacy_flag' => ['type' => 'boolean'],
                ]),
                'orderCreateOrderRequest' => self::object(['product_id' => ['type' => 'integer'], 'csr' => ['type' => 'string']]),
                'orderCreateOrderResponse' => self::object(['code' => ['type' => 'integer'], 'error' => ['$ref' => '#/definitions/errorError']]),
                'mcOrder_orderCreateOrderRequest' => self::object(['period' => ['type' => 'integer'], 'quantity' => ['type' => 'integer']]),
                'mcOrder_orderCreateOrderResponse' => self::object(['code' => ['type' => 'integer'], 'error' => ['$ref' => '#/definitions/mcOrder_errorError']]),
                'mcOrder_orderGetOrderResponse' => self::object(['code' => ['type' => 'integer']]),
                'mcOrder_errorError' => $error,
                'errorError' => $error,
                'signupCreateResellerRequest' => self::object(['email' => ['type' => 'string']]),
                'signupCreateResellerResponse' => self::object(['code' => ['type' => 'integer'], 'error' => ['$ref' => '#/definitions/errorError']]),
            ],
        ];
    }

    /**
     * @param array<string, mixed>                $methods
     * @param array<string, array<string, mixed>> $definitions
     * @param array<string, mixed>                $extraPaths
     *
     * @return array<string, mixed>
     */
    private static function service(string $path, array $methods, array $definitions = [], array $extraPaths = []): array
    {
        return ['swagger' => '2.0', 'paths' => [$path => $methods] + $extraPaths, 'definitions' => $definitions];
    }

    /**
     * @param list<array<string, mixed>> $parameters
     *
     * @return array<string, mixed>
     */
    private static function op(string $id, ?string $body, string $response, array $parameters = []): array
    {
        if ($body !== null) {
            $parameters[] = ['name' => 'body', 'in' => 'body', 'required' => true, 'schema' => ['$ref' => '#/definitions/' . $body]];
        }

        return [
            'operationId' => $id,
            'parameters' => $parameters,
            'responses' => ['200' => ['description' => 'ok', 'schema' => ['$ref' => '#/definitions/' . $response]]],
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $properties
     *
     * @return array<string, mixed>
     */
    private static function object(array $properties): array
    {
        return ['type' => 'object', 'properties' => $properties];
    }

    /**
     * @param array<array-key, mixed> $spec
     *
     * @return list<string>
     */
    private static function operationKeys(array $spec): array
    {
        $keys = [];
        foreach (self::paths($spec) as $path => $item) {
            self::assertIsArray($item);
            foreach (array_keys($item) as $method) {
                $keys[] = strtoupper((string) $method) . ' ' . $path;
            }
        }
        sort($keys);

        return $keys;
    }

    /**
     * @param array<array-key, mixed> $spec
     *
     * @return array<array-key, mixed>
     */
    private static function paths(array $spec): array
    {
        self::assertIsArray($spec['paths']);

        return $spec['paths'];
    }

    /**
     * @param array<array-key, mixed> $spec
     *
     * @return array<array-key, mixed>
     */
    private static function definitions(array $spec): array
    {
        self::assertIsArray($spec['definitions']);

        return $spec['definitions'];
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function properties(mixed $definition): array
    {
        self::assertIsArray($definition);
        self::assertIsArray($definition['properties']);

        return $definition['properties'];
    }

    /**
     * @param array<array-key, mixed> $spec
     *
     * @return array<array-key, mixed>
     */
    private static function operation(array $spec, string $path, string $method): array
    {
        $item = self::paths($spec)[$path];
        self::assertIsArray($item);
        self::assertIsArray($item[$method]);

        return $item[$method];
    }

    /**
     * @param array<array-key, mixed> $spec
     */
    private static function bodyRef(array $spec, string $path, string $method): mixed
    {
        $parameters = self::operation($spec, $path, $method)['parameters'];
        self::assertIsArray($parameters);
        foreach ($parameters as $parameter) {
            self::assertIsArray($parameter);
            if ($parameter['in'] === 'body') {
                self::assertIsArray($parameter['schema']);

                return $parameter['schema']['$ref'];
            }
        }
        self::fail('no body parameter');
    }
}
