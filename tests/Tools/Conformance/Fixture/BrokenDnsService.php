<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Conformance\Fixture;

use Ashio\OpenProvider\Client\AbstractService;
use Ashio\OpenProvider\Model\Dns\DomainTokenCreateTokenResponseData;
use Ashio\OpenProvider\Model\Dns\ZoneZone;
use Ashio\OpenProvider\Model\Dns\ZoneZoneBoolResponseData;
use Ashio\OpenProvider\Model\ResellerCustomer\TagDeleteTagResponseData;
use Ashio\OpenProvider\Response\ApiResponse;
use Ashio\OpenProvider\Spec\Operation;
use Ashio\OpenProvider\Spec\OperationSpec;
use Ashio\OpenProvider\Spec\ParameterLocation;
use Ashio\OpenProvider\Spec\ParameterSpec;

/**
 * Planted defects for the conformance audit, each one a bug the 2.x SDK had: the audit must
 * report every one of them.
 */
final class BrokenDnsService extends AbstractService
{
    /**
     * Sends a query filter in the JSON body of a DELETE and drops `provider` (2.x DNS::deleteZone).
     *
     * @return ApiResponse<ZoneZoneBoolResponseData>
     */
    #[Operation('DELETE', '/dns/zones/{name}', 'DeleteZone')]
    public function deleteZone(string $name, ?int $id = null, ?string $domainName = null, ?string $provider = null): ApiResponse
    {
        $operation = new OperationSpec('DELETE', '/dns/zones/{name}', 'DeleteZone', [
            new ParameterSpec('name', ParameterLocation::Path, true, 'string'),
            new ParameterSpec('id', ParameterLocation::Query, false, 'integer'),
            new ParameterSpec('body', ParameterLocation::Body),
        ]);

        return self::object($this->client->call($operation, [
            'name' => $name,
            'id' => $id,
            'body' => ['domain.name' => $domainName],
        ]), ZoneZoneBoolResponseData::class);
    }

    /**
     * Takes an untyped array instead of the body model.
     *
     * @param array<array-key, mixed> $body
     *
     * @return ApiResponse<DomainTokenCreateTokenResponseData>
     */
    #[Operation('POST', '/dns/domain-token', 'CreateToken')]
    public function createToken(array $body): ApiResponse
    {
        return self::object($this->call('CreateToken', ['body' => $body]), DomainTokenCreateTokenResponseData::class);
    }

    /**
     * Declares a path the spec does not have.
     *
     * @return ApiResponse<ZoneZone>
     */
    #[Operation('GET', '/dns/zone/{name}', 'GetZone')]
    public function getZone(string $name): ApiResponse
    {
        return self::object($this->call('GetZone', ['name' => $name]), ZoneZone::class);
    }

    /**
     * A second method for an operation another service already implements.
     *
     * @return ApiResponse<TagDeleteTagResponseData>
     */
    #[Operation('DELETE', '/tags', 'DeleteTag')]
    public function deleteTagAgain(?string $key = null): ApiResponse
    {
        return self::object($this->call('DeleteTag', ['key' => $key]), TagDeleteTagResponseData::class);
    }

    /**
     * Public, but no operation.
     */
    public function helper(): string
    {
        return 'not an operation';
    }
}
