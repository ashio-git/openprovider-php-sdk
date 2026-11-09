<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Generate;

use Ashio\OpenProvider\Tests\Support\ArrayPath;
use Ashio\OpenProvider\Tools\Generate\OperationGenerator;
use Ashio\OpenProvider\Tools\Spec\SpecException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * `tools/generate operations` writes one service per tag group, one method per operation with
 * its #[Operation], the parameter registry the core encodes from, and the OpenProvider facade.
 */
final class OperationGeneratorTest extends TestCase
{
    public function testOneServicePerTagGroupWithOperationsAndTheDeferredGroupLeftOut(): void
    {
        $files = self::generate();

        self::assertSame([
            'OpenProvider.php',
            'Service/AuthService.php',
            'Service/DnsService.php',
            'Service/Operations.php',
            'Service/ResellerCustomerService.php',
        ], array_keys($files));
        self::assertArrayHasKey('Mailcow', OperationGenerator::DEFERRED_GROUPS);
        self::assertStringNotContainsString('CreateMailcowOrder', implode('', $files));
    }

    public function testAMethodCarriesItsOperationAndTakesPathThenBodyThenOptionalQuery(): void
    {
        $dns = self::generate()['Service/DnsService.php'];

        self::assertStringContainsString(<<<'PHP'
                #[Operation('GET', '/dns/zones/{name}/groups/{id}', 'GetZone')]
                public function getZone(
                    string $name,
                    int $id,
                    ?bool $withRecords = null,
                    ?string $domainName = null,
                    ?array $columns = null,
                    int|float|null $ratio = null,
                ): ApiResponse {
                    return self::object($this->call('GetZone', [
                        'name' => $name,
                        'id' => $id,
                        'with_records' => $withRecords,
                        'domain.name' => $domainName,
                        'columns' => $columns,
                        'ratio' => $ratio,
                    ]), ZoneZone::class);
                }
            PHP, $dns);
        self::assertStringContainsString(<<<'PHP'
                #[Operation('PUT', '/dns/zones/{name}', 'UpdateZone')]
                public function updateZone(string $name, ZoneUpdateZoneRequest $body): ApiResponse
                {
                    return self::object($this->call('UpdateZone', [
                        'name' => $name,
                        'body' => $body,
                    ]), ZoneZone::class);
                }
            PHP, $dns);
    }

    public function testTheDocblockNamesTheApiParameterAndTheTypedResponse(): void
    {
        $dns = self::generate()['Service/DnsService.php'];

        self::assertStringContainsString(<<<'PHP'
                /**
                 * Get a zone
                 *
                 * Returns one zone * / with its records.
                 *
                 * @param string $name Zone name
                 * @param int $id
                 * @param bool|null $withRecords (`with_records`, API default: false)
                 * @param string|null $domainName Domain name (`domain.name`)
                 * @param list<string>|null $columns Columns to return (one of: id, name)
                 * @param int|float|null $ratio
                 *
                 * @return ApiResponse<ZoneZone>
                 *
            PHP, $dns);
        self::assertStringContainsString('use Ashio\OpenProvider\Model\Dns\ZoneZone;', $dns);
        self::assertStringContainsString('use Ashio\OpenProvider\Spec\Operation;', $dns);
        self::assertStringContainsString('final class DnsService extends AbstractService', $dns);
        self::assertStringContainsString(OperationGenerator::MARKER, $dns);
    }

    public function testAnInlineListResponseIsReadItemByItem(): void
    {
        $dns = self::generate()['Service/DnsService.php'];

        self::assertStringContainsString('@return ApiResponse<list<ZoneZone>>', $dns);
        self::assertStringContainsString("return self::objects(\$this->call('ListZones', []), ZoneZone::class);", $dns);
        self::assertStringContainsString('public function listZones(): ApiResponse', $dns);
    }

    public function testMixinSuffixesAreDroppedAndGroupNamesBecomeClassNames(): void
    {
        $files = self::generate();

        self::assertStringContainsString(
            "#[Operation('DELETE', '/customers/{handle}', 'DeleteCustomerMixin8')]\n    public function deleteCustomer(string \$handle, ?bool \$force = null): ApiResponse",
            $files['Service/ResellerCustomerService.php'],
        );
        self::assertStringContainsString('final class ResellerCustomerService extends AbstractService', $files['Service/ResellerCustomerService.php']);
    }

    public function testExperimentalOperationsSaySoAndPublicOnesSkipTheToken(): void
    {
        $files = self::generate();

        self::assertStringContainsString(
            "     * @experimental Documented only by the developer portal (https://portal.example/spec.js); it may change without notice.\n     */\n    #[Operation('POST', '/resellers/signup', 'CreateResellerPublic')]",
            $files['Service/AuthService.php'],
        );
        $registry = $files['Service/Operations.php'];
        self::assertStringContainsString(<<<'PHP'
                        'Login' => new OperationSpec('POST', '/auth/login', 'Login', [
                            new ParameterSpec('body', ParameterLocation::Body, true),
                        ], requiresAuth: false),
            PHP, $registry);
        self::assertStringContainsString("'CreateResellerPublic' => new OperationSpec('POST', '/resellers/signup', 'CreateResellerPublic', [", $registry);
        self::assertStringContainsString(<<<'PHP'
                        'GetZone' => new OperationSpec('GET', '/dns/zones/{name}/groups/{id}', 'GetZone', [
                            new ParameterSpec('id', ParameterLocation::Path, true, 'integer'),
                            new ParameterSpec('name', ParameterLocation::Path, true, 'string'),
                            new ParameterSpec('with_records', ParameterLocation::Query, false, 'boolean'),
                            new ParameterSpec('domain.name', ParameterLocation::Query, false, 'string'),
                            new ParameterSpec('columns', ParameterLocation::Query, false, 'array', 'string', 'multi'),
                            new ParameterSpec('ratio', ParameterLocation::Query, false, 'number'),
                        ]),
            PHP, $registry);
        self::assertStringContainsString("'ListZones' => new OperationSpec('GET', '/dns/zones', 'ListZones', []),", $registry);
        self::assertStringContainsString("public const IDS = [\n        'Login',\n        'CreateResellerPublic',\n        'GetZone',", $registry);
    }

    public function testTheFacadeHasOneAccessorPerService(): void
    {
        $facade = self::generate()['OpenProvider.php'];

        self::assertStringContainsString(<<<'PHP'
                public function resellerCustomer(): ResellerCustomerService
                {
                    return $this->resellerCustomer ??= new ResellerCustomerService($this->client);
                }
            PHP, $facade);
        self::assertStringContainsString('public function dns(): DnsService', $facade);
        self::assertStringContainsString('public function auth(): AuthService', $facade);
        self::assertStringNotContainsString('mailcow', strtolower($facade));
    }

    public function testTheOutputIsAPureFunctionOfTheInputs(): void
    {
        self::assertSame(self::generate(), self::generate());
    }

    /**
     * @return iterable<string, array{\Closure(array<array-key, mixed>): array<array-key, mixed>, string}>
     */
    public static function refusedSpecs(): iterable
    {
        yield 'two operations take one method name' => [
            static fn (array $spec): array => ArrayPath::with($spec, ArrayPath::at($spec, 'paths', '/dns/zones', 'get'), 'paths', '/dns/zones', 'post'),
            'DnsService::listZones() would implement both',
        ];
        yield 'an operation without a tag group' => [
            static fn (array $spec): array => ArrayPath::with($spec, ['Lost'], 'paths', '/dns/zones', 'get', 'tags'),
            'ListZones: the tag "Lost" is in no x-tagGroups group',
        ];
        yield 'a method name the base class owns' => [
            static fn (array $spec): array => ArrayPath::with($spec, 'Call', 'paths', '/dns/zones', 'get', 'operationId'),
            'Call: the method name call() is reserved',
        ];
        yield 'data that is neither a model nor a list of models' => [
            static fn (array $spec): array => ArrayPath::with($spec, ['type' => 'string'], 'definitions', 'dns.zoneListZonesResponse', 'properties', 'data'),
            'ListZones: data is neither a $ref to a model nor a list of them',
        ];
        yield 'two query parameters with one PHP name' => [
            static fn (array $spec): array => ArrayPath::with($spec, [
                ['name' => 'domain.name', 'in' => 'query', 'type' => 'string'],
                ['name' => 'domain_name', 'in' => 'query', 'type' => 'string'],
            ], 'paths', '/dns/zones', 'get', 'parameters'),
            'both map to the property $domainName',
        ];
    }

    /**
     * @param \Closure(array<array-key, mixed>): array<array-key, mixed> $break
     */
    #[DataProvider('refusedSpecs')]
    public function testASpecTheGeneratorCannotMapFaithfullyIsRefused(\Closure $break, string $message): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage($message);

        OperationGenerator::generate(self::json($break(self::spec())), self::behavior());
    }

    /**
     * @return array<string, string>
     */
    private static function generate(): array
    {
        return OperationGenerator::generate(self::json(self::spec()), self::behavior());
    }

    private static function behavior(): string
    {
        return self::json(['authentication' => ['login' => ['operation' => 'POST /auth/login']]]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function spec(): array
    {
        $ref = static fn (string $name): array => ['$ref' => '#/definitions/' . $name];
        $envelope = static fn (array $data): array => ['type' => 'object', 'properties' => ['code' => ['type' => 'integer'], 'desc' => ['type' => 'string'], 'data' => $data]];
        $responses = static fn (string $envelope): array => [
            '200' => ['schema' => $ref($envelope)],
            'default' => ['schema' => $ref('dns.errorError')],
        ];
        $body = static fn (string $definition): array => ['name' => 'body', 'in' => 'body', 'required' => true, 'schema' => $ref($definition)];

        return [
            'x-openprovider-sources' => ['portal' => ['url' => 'https://portal.example/spec.js']],
            'x-tagGroups' => [
                ['name' => 'Introduction', 'tags' => ['descAbout']],
                ['name' => 'Auth', 'tags' => ['Auth']],
                ['name' => 'DNS', 'tags' => ['ZoneService', 'NsGroupService']],
                ['name' => 'Reseller/Customer', 'tags' => ['Customer']],
                ['name' => 'Mailcow', 'tags' => ['MailcowOrder']],
            ],
            'paths' => [
                '/auth/login' => ['post' => [
                    'tags' => ['Auth'],
                    'summary' => 'Log in',
                    'operationId' => 'Login',
                    'parameters' => [$body('auth.authLoginRequest')],
                    'responses' => $responses('auth.authLoginResponse'),
                ]],
                '/resellers/signup' => ['post' => [
                    'tags' => ['Auth'],
                    'operationId' => 'CreateResellerPublic',
                    'security' => [],
                    'x-experimental' => true,
                    'parameters' => [$body('auth.authLoginRequest')],
                    'responses' => $responses('auth.authLoginResponse'),
                ]],
                '/dns/zones/{name}/groups/{id}' => ['get' => [
                    'tags' => ['ZoneService'],
                    'summary' => 'Get a zone',
                    'description' => 'Returns one zone */ with its records.',
                    'operationId' => 'GetZone',
                    'parameters' => [
                        ['name' => 'id', 'in' => 'path', 'required' => true, 'type' => 'integer', 'format' => 'int32'],
                        ['name' => 'name', 'in' => 'path', 'required' => true, 'type' => 'string', 'description' => 'Zone name'],
                        ['name' => 'with_records', 'in' => 'query', 'type' => 'boolean', 'default' => false],
                        ['name' => 'domain.name', 'in' => 'query', 'type' => 'string', 'description' => 'Domain name'],
                        ['name' => 'columns', 'in' => 'query', 'type' => 'array', 'items' => ['type' => 'string', 'enum' => ['id', 'name']], 'collectionFormat' => 'multi', 'description' => 'Columns to return'],
                        ['name' => 'ratio', 'in' => 'query', 'type' => 'number', 'format' => 'double'],
                    ],
                    'responses' => $responses('dns.zoneGetZoneResponse'),
                ]],
                '/dns/zones/{name}' => ['put' => [
                    'tags' => ['ZoneService'],
                    'operationId' => 'UpdateZone',
                    'parameters' => [
                        ['name' => 'name', 'in' => 'path', 'required' => true, 'type' => 'string'],
                        $body('dns.zoneUpdateZoneRequest'),
                    ],
                    'responses' => $responses('dns.zoneGetZoneResponse'),
                ]],
                '/dns/zones' => ['get' => [
                    'tags' => ['NsGroupService'],
                    'operationId' => 'ListZones',
                    'responses' => $responses('dns.zoneListZonesResponse'),
                ]],
                '/customers/{handle}' => ['delete' => [
                    'tags' => ['Customer'],
                    'operationId' => 'DeleteCustomerMixin8',
                    'parameters' => [
                        ['name' => 'handle', 'in' => 'path', 'required' => true, 'type' => 'string'],
                        ['name' => 'force', 'in' => 'query', 'type' => 'boolean'],
                    ],
                    'responses' => $responses('dns.zoneGetZoneResponse'),
                ]],
                '/mailcow/orders' => ['post' => [
                    'tags' => ['MailcowOrder'],
                    'operationId' => 'CreateMailcowOrder',
                    'parameters' => [$body('dns.zoneUpdateZoneRequest')],
                    'responses' => $responses('dns.zoneGetZoneResponse'),
                ]],
            ],
            'definitions' => [
                'auth.authLoginRequest' => ['type' => 'object', 'properties' => ['username' => ['type' => 'string']]],
                'auth.authLoginResponse' => $envelope($ref('auth.authLoginResponseData')),
                'auth.authLoginResponseData' => ['type' => 'object', 'properties' => ['token' => ['type' => 'string']]],
                'dns.errorError' => ['type' => 'object', 'properties' => ['code' => ['type' => 'integer'], 'desc' => ['type' => 'string']]],
                'dns.zoneGetZoneResponse' => $envelope($ref('dns.zoneZone')),
                'dns.zoneListZonesResponse' => $envelope(['type' => 'array', 'items' => $ref('dns.zoneZone')]),
                'dns.zoneUpdateZoneRequest' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string']]],
                'dns.zoneZone' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string']]],
            ],
        ];
    }

    /**
     * @param array<array-key, mixed> $value
     */
    private static function json(array $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }
}
