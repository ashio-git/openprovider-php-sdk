<?php

namespace Vexura\Domain;

use GuzzleHttp\Exception\GuzzleException;
use Vexura\Exception\ApiException;
use Vexura\Exception\AuthenticationException;
use Vexura\Exception\NotFoundException;
use Vexura\Exception\ValidationException;
use Vexura\License\License;
use Vexura\OpenProvider;
use Vexura\Model\AdditionalDataSpecification;
use Vexura\Model\AuthCode;
use Vexura\Model\CheckDomainResult;
use Vexura\Model\DeleteDomainResponse;
use Vexura\Model\Domain as DomainModel;
use Vexura\Model\Price;
use Vexura\Model\RenewDomainResponse;
use Vexura\Model\RestoreDomainResponse;
use Vexura\Model\SuggestNameDomainResult;
use Vexura\Model\Tld;
use Vexura\Model\TradeDomainResponse;
use Vexura\Model\TransferDomainResponse;

class Domain
{
    private OpenProvider $API;
    private ?DNS $dnsHandler = null;
    private ?EasyDMARC $easyDMARCHandler = null;
    private ?SpamExpert $spamExpertHandler = null;

    public function __construct(OpenProvider $API)
    {
        $this->API = $API;
    }

    /**
     * @return DNS
     */
    public function dns(): DNS
    {
        if (!$this->dnsHandler) {
            $this->dnsHandler = new DNS($this->API);
        }
        return $this->dnsHandler;
    }

    /**
     * @return EasyDMARC
     */
    public function easyDMARC(): EasyDMARC
    {
        if (!$this->easyDMARCHandler) {
            $this->easyDMARCHandler = new EasyDMARC($this->API);
        }
        return $this->easyDMARCHandler;
    }

    /**
     * @return SpamExpert
     */
    public function spamExpert(): SpamExpert
    {
        if (!$this->spamExpertHandler) {
            $this->spamExpertHandler = new SpamExpert($this->API);
        }
        return $this->spamExpertHandler;
    }

    /**
     * Get the domain price list.
     * @return Price
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getPricelist(): Price
    {
        $response = $this->API->get('domains/prices');
        return (new Price())->fromArray($response['data']);
    }

    /**
     * Checks the availability of one or more domain names.
     * @param string[] $domainNames An array of domain names to check (e.g., ['example.com', 'test.net']).
     * @return CheckDomainResult[]
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function check(array $domainNames): array
    {
        $payload = ['domains' => []];
        foreach ($domainNames as $domainName) {
            $parts = explode('.', $domainName, 2);
            $payload['domains'][] = [
                'name' => $parts[0],
                'extension' => $parts[1] ?? ''
            ];
        }

        $response = $this->API->post('domains/check', $payload);
        $results = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $result) {
                $results[] = (new CheckDomainResult())->fromArray($result);
            }
        }
        return $results;
    }

    /**
     * Get a specific domain by its ID.
     * @param int $id The ID of the domain.
     * @return DomainModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getDomain(int $id): DomainModel
    {
        $response = $this->API->get("domains/{$id}");
        return new DomainModel($response['data'] ?? []);
    }

    /**
     * List all domains with optional filters.
     * @return \Vexura\Model\PaginatedResponse
     * @phpstan-return \Vexura\Model\PaginatedResponse<DomainModel>
     * @psalm-return \Vexura\Model\PaginatedResponse<DomainModel>
     */
    public function listDomains(
        int $limit = 100,
        int $offset = 0,
        ?string $extension = null,
        ?string $status = null,
        ?string $domainNamePattern = null,
        ?bool $withHistory = null,
        ?bool $withAdditionalData = null
    ): \Vexura\Model\PaginatedResponse {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'extension' => $extension,
            'status' => $status,
            'domain_name_pattern' => $domainNamePattern,
            'with_history' => $withHistory,
            'with_additional_data' => $withAdditionalData,
        ], static fn($v) => $v !== null);

        $response = $this->API->get('domains', $params);
        $domains = [];
        foreach ($response['data']['results'] ?? [] as $domainData) {
            $domains[] = new DomainModel($domainData);
        }
        return new \Vexura\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $domains);
    }

    /**
     * Create a new domain.
     * @param array $data The data for the new domain.
     * @return DomainModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createDomain(\Vexura\Model\DomainCreateRequest $request): DomainModel
    {
        $response = $this->API->post('domains', $request->toArray());
        return (new DomainModel())->fromArray($response['data']);
    }

    /**
     * Update an existing domain.
     * @param int $id The ID of the domain to update.
     * @param array $data The data to update.
     * @return DomainModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateDomain(int $id, \Vexura\Model\DomainUpdateRequest $request): DomainModel
    {
        $response = $this->API->put("domains/{$id}", $request->toArray());
        return (new DomainModel())->fromArray($response['data']);
    }

    /**
     * Delete a domain.
     * @param int $id The ID of the domain to delete.
     * @param array $params Optional parameters.
     * @return DeleteDomainResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function deleteDomain(int $id, array $params = []): DeleteDomainResponse
    {
        $response = $this->API->delete("domains/{$id}", $params);
        return (new DeleteDomainResponse())->fromArray($response['data']);
    }

    /**
     * Renew a domain.
     * @param int $id The ID of the domain to renew.
     * @param array $data The renewal data.
     * @return RenewDomainResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function renewDomain(int $id, array $data): RenewDomainResponse
    {
        $response = $this->API->post("domains/{$id}/renew", $data);
        return (new RenewDomainResponse())->fromArray($response['data']);
    }

    /**
     * Restore a domain from quarantine.
     * @param int $id The ID of the domain to restore.
     * @param array $data The restore data.
     * @return RestoreDomainResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function restoreDomain(int $id, array $data): RestoreDomainResponse
    {
        $response = $this->API->post("domains/{$id}/restore", $data);
        return (new RestoreDomainResponse())->fromArray($response['data']);
    }

    /**
     * Approve a domain transfer.
     * @param int $id The ID of the domain transfer to approve.
     * @param array $data The approval data.
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function approveTransfer(int $id, array $data): \Vexura\Model\ActionResponse
    {
        $response = $this->API->post("domains/{$id}/transfer/approve", $data);
        return (new \Vexura\Model\ActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Request a domain transfer.
     * @param array $data The transfer data.
     * @return TransferDomainResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function transferDomain(array $data): TransferDomainResponse
    {
        $response = $this->API->post("domains/transfer", $data);
        return (new TransferDomainResponse())->fromArray($response['data']);
    }

    /**
     * Request a domain trade (change of ownership).
     * @param array $data The trade data.
     * @return TradeDomainResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function tradeDomain(array $data): TradeDomainResponse
    {
        $response = $this->API->post("domains/trade", $data);
        return (new TradeDomainResponse())->fromArray($response['data']);
    }

    /**
     * Suggest alternative domain names.
     * @param array $data The suggestion criteria.
     * @return SuggestNameDomainResult[]
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function suggestName(array $data): array
    {
        $response = $this->API->post("domains/suggest-name", $data);
        $results = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $result) {
                $results[] = (new SuggestNameDomainResult())->fromArray($result);
            }
        }
        return $results;
    }

    /**
     * Send the Form of Authorization (FOA) for a domain transfer.
     * @param int $id The ID of the domain.
     * @param array $data The FOA data.
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function sendFoa1(int $id, array $data): \Vexura\Model\ActionResponse
    {
        $response = $this->API->post("domains/{$id}/transfer/send-foa1", $data);
        return (new \Vexura\Model\ActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Get the authorization code for a domain.
     * @param int $id The ID of the domain.
     * @return \Vexura\Model\AuthCode
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Get authorization code for a domain
     * @return \Vexura\Model\AuthCode
     */
    public function getAuthCode(int $id): \Vexura\Model\AuthCode
    {
        $response = $this->API->get("domains/{$id}/authcode");
        return (new \Vexura\Model\AuthCode())->fromArray($response['data'] ?? []);
    }

    /**
     * Reset the authorization code for a domain.
     * @param int $id The ID of the domain.
     * @param array $data The reset data.
     * @return \Vexura\Model\AuthCode
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Reset authorization code for a domain
     * @return \Vexura\Model\AuthCode
     */
    public function resetAuthCode(int $id, array $data): \Vexura\Model\AuthCode
    {
        $response = $this->API->post("domains/{$id}/authcode/reset", $data);
        return (new \Vexura\Model\AuthCode())->fromArray($response['data'] ?? []);
    }

    /**
     * Retry the last failed operation for a domain.
     * @param int $id The ID of the domain.
     * @param array $data The retry data.
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function tryAgainLastOperation(int $id, array $data): bool
    {
        $response = $this->API->post("domains/{$id}/last-operation/restart", $data);
        return (bool)($response['data']['success'] ?? false);
    }

    /**
     * Get additional data required for a specific TLD.
     * @param array $params The parameters to get additional data.
     * @return \Vexura\Model\AdditionalDataSpecification[]
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getAdditionalData(array $params = [])
    {
        $response = $this->API->get('domains/additional-data', $params);
        $specs = [];
        foreach (($response['data'] ?? []) as $row) {
            $specs[] = (new \Vexura\Model\AdditionalDataSpecification())->fromArray($row);
        }
        return $specs;
    }

    /**
     * Get additional data required for a specific customer.
     * @param array $params The parameters to get customer additional data.
     * @return \Vexura\Model\AdditionalDataSpecification[]
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getCustomerAdditionalData(array $params = [])
    {
        $response = $this->API->get('domains/additional-data/customers', $params);
        $specs = [];
        foreach (($response['data'] ?? []) as $row) {
            $specs[] = (new \Vexura\Model\AdditionalDataSpecification())->fromArray($row);
        }
        return $specs;
    }

    /**
     * List all TLDs.
     * @param array $params Optional parameters to filter the list of TLDs.
     * @return \Vexura\Model\PaginatedResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * List TLDs.
     * @return \Vexura\Model\PaginatedResponse
     * @phpstan-return \Vexura\Model\PaginatedResponse<Tld>
     * @psalm-return \Vexura\Model\PaginatedResponse<Tld>
     */
    public function listTlds(int $limit = 100, int $offset = 0, ?string $namePattern = null, bool $withPrice = false, bool $withDescription = false): \Vexura\Model\PaginatedResponse
    {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'name_pattern' => $namePattern,
            'with_price' => $withPrice,
            'with_description' => $withDescription,
        ]);

        $response = $this->API->get('tlds', $params);
        $tlds = [];
        foreach ($response['data']['results'] ?? [] as $tldData) {
            $tlds[] = new Tld($tldData);
        }
        return new \Vexura\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $tlds);
    }

    /**
     * Get a specific TLD.
     * @param string $name The name of the TLD.
     * @param array $params Optional parameters.
     * @return Tld
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getTld(string $name, array $params = []): Tld
    {
        $response = $this->API->get("tlds/{$name}", $params);
        return new Tld($response['data'] ?? []);
    }
}
