<?php

namespace Ashio\License;

use GuzzleHttp\Exception\GuzzleException;
use Ashio\Exception\ApiException;
use Ashio\Exception\AuthenticationException;
use Ashio\Exception\NotFoundException;
use Ashio\Exception\ValidationException;
use Ashio\OpenProvider;
use Ashio\Model\License as LicenseModel;
use Ashio\Model\LicenseResetHwidRequest;

class License
{
    private OpenProvider $API;

    public function __construct(OpenProvider $API)
    {
        $this->API = $API;
    }

    /**
     * List all licenses with optional filters.
     * @param int $limit
     * @param int $offset
     * @param string|null $status
     * @param string|null $product
     * @return array
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Ashio\Model\PaginatedResponse
     * @phpstan-return \Ashio\Model\PaginatedResponse<LicenseModel>
     * @psalm-return \Ashio\Model\PaginatedResponse<LicenseModel>
     */
    public function listLicenses(int $limit = 100, int $offset = 0, ?string $status = null, ?string $product = null): \Ashio\Model\PaginatedResponse
    {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'status' => $status,
            'product' => $product,
        ]);

        $response = $this->API->get('licenses', $params);
        $licenses = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $licenseData) {
                $licenses[] = new LicenseModel($licenseData);
            }
        }
        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $licenses);
    }

    /**
     * Reset the Hardware ID (HWID) for a license.
     * @param string $product
     * @param int $keyId
     * @param LicenseResetHwidRequest $request
     * @return \Ashio\Model\ActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Reset the Hardware ID (HWID) for a license.
     * @return bool
     */
    public function resetHwid(string $product, int $keyId, LicenseResetHwidRequest $request): \Ashio\Model\ActionResponse
    {
        $response = $this->API->post("licenses/hwids/reset/{$product}/{$keyId}", $request->toArray());
        return (new \Ashio\Model\ActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * List all license items with optional filters.
     * @param array $params
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Ashio\Model\PaginatedResponse
     * @phpstan-return \Ashio\Model\PaginatedResponse<\Ashio\Model\LicenseItem>
     * @psalm-return \Ashio\Model\PaginatedResponse<\Ashio\Model\LicenseItem>
     */
    public function listItems(
        int $limit = 100,
        int $offset = 0,
        ?string $product = null,
        ?string $status = null,
        ?string $titlePattern = null,
        ?int $keyId = null,
        ?string $keyNumber = null
    ): \Ashio\Model\PaginatedResponse {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'product' => $product,
            'status' => $status,
            'title_pattern' => $titlePattern,
            'key_id' => $keyId,
            'key_number' => $keyNumber,
        ], static fn($v) => $v !== null);

        $response = $this->API->get('licenses/items', $params);
        $items = [];
        foreach ($response['data']['results'] ?? [] as $row) {
            $items[] = (new \Ashio\Model\LicenseItem())->fromArray($row);
        }
        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $items);
    }

    /**
     * List all Plesk licenses with optional filters.
     * @param array $params
     * @return array
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Ashio\Model\PaginatedResponse
     * @phpstan-return \Ashio\Model\PaginatedResponse<LicenseModel>
     * @psalm-return \Ashio\Model\PaginatedResponse<LicenseModel>
     */
    public function listPleskLicenses(
        int $limit = 100,
        int $offset = 0,
        ?int $keyId = null,
        ?string $keyNumber = null,
        ?string $titlePattern = null,
        ?string $keyNumberPattern = null,
        ?int $parentKeyId = null
    ): \Ashio\Model\PaginatedResponse {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'key_id' => $keyId,
            'key_number' => $keyNumber,
            'title_pattern' => $titlePattern,
            'key_number_pattern' => $keyNumberPattern,
            'parent_key_id' => $parentKeyId,
        ], static fn($v) => $v !== null);

        $response = $this->API->get('licenses/plesk', $params);
        $licenses = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $licenseData) {
                $licenses[] = new LicenseModel($licenseData);
            }
        }
        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $licenses);
    }

    /**
     * Create a new Plesk license.
     * @param \Ashio\Model\CreatePleskLicenseRequest $request
     * @return LicenseModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createPleskLicense(\Ashio\Model\CreatePleskLicenseRequest $request): LicenseModel
    {
        $response = $this->API->post('licenses/plesk', $request->toArray());
        return new LicenseModel($response['data'] ?? []);
    }

    /**
     * Get a specific Plesk license key.
     * @param int $keyId
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Get a specific Plesk license key.
     * @return \Ashio\Model\LicenseKey
     */
    public function getPleskKey(int $keyId): \Ashio\Model\LicenseKey
    {
        $response = $this->API->get("licenses/plesk/key/{$keyId}");
        return (new \Ashio\Model\LicenseKey())->fromArray($response['data'] ?? []);
    }

    /**
     * Get a specific Plesk license by its key ID.
     * @param int $keyId
     * @return LicenseModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getPleskLicense(int $keyId): LicenseModel
    {
        $response = $this->API->get("licenses/plesk/{$keyId}");
        return new LicenseModel($response['data'] ?? []);
    }

    /**
     * Update an existing Plesk license.
     * @param int $keyId
     * @param \Ashio\Model\UpdatePleskLicenseRequest $request
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updatePleskLicense(int $keyId, \Ashio\Model\UpdatePleskLicenseRequest $request): bool
    {
        $response = $this->API->put("licenses/plesk/{$keyId}", $request->toArray());
        return (bool)($response['data']['success'] ?? false);
    }

    /**
     * Delete a Plesk license.
     * @param int $keyId
     * @return \Ashio\Model\LicenseKey
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Delete a Plesk license.
     * @return \Ashio\Model\ActionResponse
     */
    public function deletePleskLicense(int $keyId): \Ashio\Model\ActionResponse
    {
        $response = $this->API->delete("licenses/plesk/{$keyId}");
        return (new \Ashio\Model\ActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * List all Virtuozzo licenses with optional filters.
     * @param array $params
     * @return array
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Ashio\Model\PaginatedResponse
     * @phpstan-return \Ashio\Model\PaginatedResponse<LicenseModel>
     * @psalm-return \Ashio\Model\PaginatedResponse<LicenseModel>
     */
    public function listVirtuozzoLicenses(
        int $limit = 100,
        int $offset = 0,
        ?int $keyId = null,
        ?string $keyNumber = null,
        ?string $titlePattern = null,
        ?string $keyNumberPattern = null,
        ?int $parentKeyId = null
    ): \Ashio\Model\PaginatedResponse {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'key_id' => $keyId,
            'key_number' => $keyNumber,
            'title_pattern' => $titlePattern,
            'key_number_pattern' => $keyNumberPattern,
            'parent_key_id' => $parentKeyId,
        ], static fn($v) => $v !== null);

        $response = $this->API->get('licenses/virtuozzo', $params);
        $licenses = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $licenseData) {
                $licenses[] = new LicenseModel($licenseData);
            }
        }
        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $licenses);
    }

    /**
     * Create a new Virtuozzo license.
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Create a new Virtuozzo license.
     * @param LicenseModel $license
     * @return LicenseModel
     */
    public function createVirtuozzoLicense(\Ashio\Model\CreateVirtuozzoLicenseRequest $request): LicenseModel
    {
        $response = $this->API->post('licenses/virtuozzo', $request->toArray());
        return new LicenseModel($response['data'] ?? []);
    }

    /**
     * Get a specific Virtuozzo license by its key ID.
     * @param int $keyId
     * @return LicenseModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getVirtuozzoLicense(int $keyId): LicenseModel
    {
        $response = $this->API->get("licenses/virtuozzo/{$keyId}");
        return new LicenseModel($response['data'] ?? []);
    }

    /**
     * Update an existing Virtuozzo license.
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Update an existing Virtuozzo license.
     * @param int $keyId
     * @param LicenseModel $license
     * @return bool
     */
    public function updateVirtuozzoLicense(int $keyId, \Ashio\Model\UpdateVirtuozzoLicenseRequest $request): bool
    {
        $response = $this->API->put("licenses/virtuozzo/{$keyId}", $request->toArray());
        return (bool)($response['data']['success'] ?? false);
    }

    /**
     * Delete a Virtuozzo license.
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Delete a Virtuozzo license.
     * @return \Ashio\Model\ActionResponse
     */
    public function deleteVirtuozzoLicense(int $keyId): \Ashio\Model\ActionResponse
    {
        $response = $this->API->delete("licenses/virtuozzo/{$keyId}");
        return (new \Ashio\Model\ActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Get a specific Virtuozzo license key.
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Get a specific Virtuozzo license key.
     * @return \Ashio\Model\LicenseKey
     */
    public function getVirtuozzoKey(int $keyId): \Ashio\Model\LicenseKey
    {
        $response = $this->API->get("licenses/virtuozzo/{$keyId}/key");
        return (new \Ashio\Model\LicenseKey())->fromArray($response['data'] ?? []);
    }
}
