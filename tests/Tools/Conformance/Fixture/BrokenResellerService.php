<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Conformance\Fixture;

use Ashio\OpenProvider\Client\AbstractService;
use Ashio\OpenProvider\Model\ResellerCustomer\CustomerListCustomersResponseData;
use Ashio\OpenProvider\Model\ResellerCustomer\SettingsGetSettingsResponseData;
use Ashio\OpenProvider\Model\ResellerCustomer\TagDeleteTagResponseData;
use Ashio\OpenProvider\Response\ApiResponse;
use Ashio\OpenProvider\Spec\Operation;
use Ashio\OpenProvider\Spec\OperationSpec;
use Ashio\OpenProvider\Spec\ParameterLocation;
use Ashio\OpenProvider\Spec\ParameterSpec;

/**
 * Planted defects (see BrokenDnsService), plus one operation done right.
 */
final class BrokenResellerService extends AbstractService
{
    /**
     * Sends its filter in camelCase, which the API ignores (2.x ResellerCustomer::listCustomers).
     *
     * @return ApiResponse<CustomerListCustomersResponseData>
     */
    #[Operation('GET', '/customers', 'ListCustomers')]
    public function listCustomers(?int $resellerId = null): ApiResponse
    {
        $operation = new OperationSpec('GET', '/customers', 'ListCustomers', [
            new ParameterSpec('resellerId', ParameterLocation::Query, false, 'integer'),
        ]);

        return self::object($this->client->call($operation, ['resellerId' => $resellerId]), CustomerListCustomersResponseData::class);
    }

    /**
     * Drops false with array_filter() and hands back the raw data.
     *
     * @return ApiResponse<mixed>
     */
    #[Operation('GET', '/resellers', 'GetReseller')]
    public function getReseller(?bool $withSettings = null): ApiResponse
    {
        return $this->call('GetReseller', array_filter(['with_settings' => $withSettings]));
    }

    /**
     * Types a string filter as an integer.
     *
     * @return ApiResponse<TagDeleteTagResponseData>
     */
    #[Operation('DELETE', '/tags', 'DeleteTag')]
    public function deleteTag(?int $key = null, ?string $value = null): ApiResponse
    {
        return self::object($this->call('DeleteTag', ['key' => $key, 'value' => $value]), TagDeleteTagResponseData::class);
    }

    /**
     * Conform: the audit must not report it.
     *
     * @return ApiResponse<SettingsGetSettingsResponseData>
     */
    #[Operation('GET', '/resellers/settings', 'GetSettings')]
    public function getSettings(): ApiResponse
    {
        return self::object($this->call('GetSettings', []), SettingsGetSettingsResponseData::class);
    }
}
