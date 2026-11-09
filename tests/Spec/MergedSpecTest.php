<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Spec;

use Ashio\OpenProvider\Tests\Tools\Spec\FakeFetcher;
use Ashio\OpenProvider\Tools\Spec\Json;
use Ashio\OpenProvider\Tools\Spec\MergeResult;
use Ashio\OpenProvider\Tools\Spec\Overrides;
use Ashio\OpenProvider\Tools\Spec\PortalBundle;
use Ashio\OpenProvider\Tools\Spec\Sha256Sums;
use Ashio\OpenProvider\Tools\Spec\SpecInputs;
use Ashio\OpenProvider\Tools\Spec\SpecMergeCommand;
use Ashio\OpenProvider\Tools\Spec\SpecSync;
use PHPUnit\Framework\TestCase;

/**
 * The F2 gates of docs/plans/2026-11-09-sdk-api-v1.md, on the vendored inputs in spec/.
 */
final class MergedSpecTest extends TestCase
{
    private const SPEC_DIR = __DIR__ . '/../../spec';

    private static ?MergeResult $result = null;

    public function testTheVendoredInputsMatchTheirPinnedHashes(): void
    {
        self::assertSame([], (new SpecSync(self::SPEC_DIR, new FakeFetcher([])))->verify());
    }

    public function testTheSameInputsProduceTheSameShaAsTheCommittedSpec(): void
    {
        $first = Json::encode(self::merged()->spec());
        $second = Json::encode(SpecInputs::load(self::SPEC_DIR)->merge()->spec());
        $sums = Sha256Sums::parse((string) file_get_contents(self::SPEC_DIR . '/SHA256SUMS'));

        self::assertSame(hash('sha256', $first), hash('sha256', $second));
        self::assertSame($sums->get('openprovider.json'), hash('sha256', $first));
        self::assertSame($sums->get('openprovider.json'), hash_file('sha256', self::SPEC_DIR . '/openprovider.json'));
    }

    public function testTheCommittedSpecAndReportAreWhatTheMergeWrites(): void
    {
        $out = fopen('php://memory', 'w+');
        $err = fopen('php://memory', 'w+');
        self::assertIsResource($out);
        self::assertIsResource($err);

        $exit = SpecMergeCommand::run(['--check'], self::SPEC_DIR, $out, $err);

        rewind($err);
        self::assertSame(0, $exit, (string) stream_get_contents($err));
    }

    public function testExactlyThePortalsOneHundredFortyOperationsComeOut(): void
    {
        $merged = self::operationKeys(self::merged()->spec()['paths']);
        $manifest = SpecInputs::load(self::SPEC_DIR)->manifest;
        $bundle = (string) file_get_contents(self::SPEC_DIR . '/upstream/developer-portal/swagger.spec.js');
        $portal = Json::object(Json::decode(PortalBundle::extract($bundle, $manifest->portalVariable)), 'portal');
        $portalKeys = array_map(
            static fn (string $key): string => (string) preg_replace('# /v1/#', ' /', $key, 1),
            self::operationKeys($portal['paths']),
        );

        self::assertCount(140, $merged);
        self::assertSame($portalKeys, $merged);
    }

    public function testOnlyTheWhiteLabelAndSignupOperationsComeFromThePortalAlone(): void
    {
        self::assertSame([
            'POST /mailcow/white-labels/domain',
            'POST /mailcow/white-labels/domain/upsert',
            'POST /mailcow/white-labels/logo',
            'POST /mailcow/white-labels/logo/modify',
            'POST /resellers/signup',
        ], self::merged()->portalOnlyOperations());
    }

    public function testTheThreeOperationsOutsideThePortalAreDropped(): void
    {
        self::assertSame([
            'GET /contact/notification-setting',
            'GET /payments/suggested-top-up-amount',
            'POST /contact/notification-setting',
        ], self::merged()->serviceOnlyOperations());
    }

    public function testTheSslOrderRequestComesOutWithItsFifteenFields(): void
    {
        $definitions = Json::object(self::merged()->spec()['definitions'], 'definitions');
        $ssl = Json::object(Json::object($definitions['ssl.orderCreateOrderRequest'], 'ssl')['properties'], 'properties');
        $mailcow = Json::object(Json::object($definitions['mailcow.orderCreateOrderRequest'], 'mailcow')['properties'], 'properties');

        self::assertCount(15, $ssl);
        self::assertSame([
            'approver_email', 'autorenew', 'csr', 'domain_amount', 'domain_validation_methods', 'enable_dns_automation',
            'host_names', 'organization_handle', 'period', 'product_id', 'signature_hash_algorithm', 'software_id',
            'start_provision', 'technical_handle', 'wildcard_domain_amount',
        ], array_keys($ssl));
        self::assertSame(['period', 'quantity'], array_keys($mailcow));
        self::assertSame('#/definitions/ssl.orderCreateOrderRequest', self::bodySchemaRef('/ssl/orders', 'post'));
        self::assertSame('#/definitions/mailcow.orderCreateOrderRequest', self::bodySchemaRef('/mailcow/orders', 'post'));
    }

    public function testOperationIdsAreUnique(): void
    {
        $ids = [];
        foreach (Json::object(self::merged()->spec()['paths'], 'paths') as $item) {
            foreach (Json::object($item, 'item') as $operation) {
                $ids[] = Json::string(Json::object($operation, 'operation')['operationId'], 'operationId');
            }
        }

        self::assertCount(140, array_unique($ids));
    }

    public function testWithoutTheOverridesTheCollidingOperationIdsFailTheMerge(): void
    {
        $result = SpecInputs::load(self::SPEC_DIR)->merge(Overrides::none());

        self::assertSame(
            ['operationId/CreateOrder', 'operationId/DeleteDomain', 'operationId/GetOrder'],
            array_keys($result->unresolvedConflicts()),
        );
    }

    public function testEveryReferenceResolvesAndEveryDefinitionIsUsed(): void
    {
        $spec = self::merged()->spec();
        $definitions = Json::object($spec['definitions'], 'definitions');
        $referenced = array_unique(self::references($spec));
        sort($referenced);
        $declared = array_map('strval', array_keys($definitions));
        sort($declared);

        self::assertSame($declared, $referenced);
    }

    public function testExperimentalMarksOnlyPortalOnlyContent(): void
    {
        $spec = self::merged()->spec();
        foreach (Json::object($spec['definitions'], 'definitions') as $name => $definition) {
            $isPortal = str_starts_with((string) $name, 'portal.');
            self::assertSame($isPortal, (Json::object($definition, (string) $name)['x-experimental'] ?? false) === true, (string) $name);
        }
        $experimental = [];
        foreach (Json::object($spec['paths'], 'paths') as $path => $item) {
            foreach (Json::object($item, 'item') as $method => $operation) {
                if ((Json::object($operation, 'operation')['x-experimental'] ?? false) === true) {
                    $experimental[] = strtoupper((string) $method) . ' ' . $path;
                }
            }
        }
        sort($experimental);

        self::assertSame(self::merged()->portalOnlyOperations(), $experimental);
    }

    private static function merged(): MergeResult
    {
        return self::$result ??= SpecInputs::load(self::SPEC_DIR)->merge();
    }

    /**
     * @return list<string>
     */
    private static function operationKeys(mixed $paths): array
    {
        $keys = [];
        foreach (Json::object($paths, 'paths') as $path => $item) {
            foreach (array_keys(Json::object($item, 'item')) as $method) {
                $keys[] = strtoupper((string) $method) . ' ' . $path;
            }
        }
        sort($keys);

        return $keys;
    }

    private static function bodySchemaRef(string $path, string $method): string
    {
        $paths = Json::object(self::merged()->spec()['paths'], 'paths');
        $operation = Json::object(Json::object($paths[$path], $path)[$method], $method);
        foreach (Json::list($operation['parameters'], 'parameters') as $parameter) {
            $parameter = Json::object($parameter, 'parameter');
            if ($parameter['in'] === 'body') {
                return Json::string(Json::object($parameter['schema'], 'schema')['$ref'], '$ref');
            }
        }
        self::fail("$method $path has no body");
    }

    /**
     * @return list<string>
     */
    private static function references(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $found = [];
        foreach ($value as $key => $item) {
            if ($key === '$ref' && is_string($item)) {
                $found[] = substr($item, strlen('#/definitions/'));
            } else {
                array_push($found, ...self::references($item));
            }
        }

        return $found;
    }
}
