<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Model;

use Ashio\OpenProvider\Http\RequestEncoder;
use Ashio\OpenProvider\Model\Domain\DomainAdditionalData;
use Ashio\OpenProvider\Model\Domain\DomainCreateDomainRequest;
use Ashio\OpenProvider\Model\Domain\DomainDomain;
use Ashio\OpenProvider\Model\License\LicenseKey;
use Ashio\OpenProvider\Spec\OperationSpec;
use Ashio\OpenProvider\Tests\Support\ArrayPath;
use PHPUnit\Framework\TestCase;

/**
 * A generated model is a request body as is: the core's encoder sends what toArray() holds, with
 * the zero values the API must receive and empty objects as `{}`.
 */
final class ModelEncodingTest extends TestCase
{
    public function testACreateDomainBodyKeepsZeroValuesAndSendsEmptyObjectsAsObjects(): void
    {
        $request = new DomainCreateDomainRequest(
            additionalData: new DomainAdditionalData(),
            autorenew: '',
            domain: new DomainDomain(extension: 'com', name: 'example'),
            isEasyDmarcEnabled: false,
            nameServers: [],
            period: 0,
            extra: ['future_field' => 1],
        );

        $body = (new RequestEncoder())->encode(self::operation('/domains', 'post'), ['body' => $request])->body;

        self::assertSame(
            '{"additional_data":{},"autorenew":"","domain":{"extension":"com","name":"example"},"is_easy_dmarc_enabled":false,"name_servers":[],"period":0,"future_field":1}',
            $body,
        );
    }

    public function testAnEmptyMapIsSentAsAnObject(): void
    {
        self::assertSame('{"sku_values":{}}', json_encode(new LicenseKey(skuValues: []), JSON_THROW_ON_ERROR));
    }

    public function testAModelReadBackFromAResponseIsTheBodyItWasBuiltFrom(): void
    {
        $data = ['autorenew' => 'on', 'domain' => ['extension' => 'com', 'name' => 'example', 'new_api_field' => true], 'period' => 1];

        self::assertSame($data, DomainCreateDomainRequest::fromArray($data)->toArray());
        self::assertSame(['new_api_field' => true], DomainCreateDomainRequest::fromArray($data)->domain?->extra);
    }

    private static function operation(string $path, string $method): OperationSpec
    {
        $spec = json_decode((string) file_get_contents(__DIR__ . '/../../spec/openprovider.json'), true, 512, JSON_THROW_ON_ERROR);
        $operation = ArrayPath::at($spec, 'paths', $path, $method);
        self::assertIsArray($operation);

        return OperationSpec::fromSwagger($method, $path, $operation);
    }
}
