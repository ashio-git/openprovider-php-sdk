<?php

namespace Ashio\Domain;

use GuzzleHttp\Exception\GuzzleException;
use Ashio\Exception\ApiException;
use Ashio\Exception\AuthenticationException;
use Ashio\Exception\NotFoundException;
use Ashio\Exception\ValidationException;
use Ashio\License\License;
use Ashio\OpenProvider;
use Ashio\Model\AdditionalDataSpecification;
use Ashio\Model\AuthCode;
use Ashio\Model\CheckDomainResult;
use Ashio\Model\DeleteDomainResponse;
use Ashio\Model\Domain as DomainModel;
use Ashio\Model\Price;
use Ashio\Model\RenewDomainResponse;
use Ashio\Model\RenewDomainRequest;
use Ashio\Model\RestoreDomainResponse;
use Ashio\Model\SuggestNameDomainResult;
use Ashio\Model\Tld;
use Ashio\Model\TradeDomainResponse;
use Ashio\Model\TransferDomainResponse;
use Ashio\Model\DomainTransferRequest;
use Ashio\Model\DomainTradeRequest;
use Ashio\Model\DomainSuggestNameRequest;
use Ashio\Model\DomainRestoreRequest;
use Ashio\Model\DomainApproveTransferRequest;
use Ashio\Model\DomainSendFoa1Request;
use Ashio\Model\DomainTryAgainLastOperationRequest;
use Ashio\Model\DomainResetAuthCodeRequest;

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
     * @return \Ashio\Model\PaginatedResponse
     * @phpstan-return \Ashio\Model\PaginatedResponse<DomainModel>
     * @psalm-return \Ashio\Model\PaginatedResponse<DomainModel>
     */
    public function listDomains(
        int $limit = 100,
        int $offset = 0,
        ?string $extension = null,
        ?string $status = null,
        ?string $domainNamePattern = null,
        ?bool $withHistory = null,
        ?bool $withAdditionalData = null
    ): \Ashio\Model\PaginatedResponse {
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
        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $domains);
    }

    /**
     * Create a new domain.
     * @param DomainCreateRequest $request The data for the new domain.
     * @return DomainModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createDomain(\Ashio\Model\DomainCreateRequest $request): DomainModel
    {
        $response = $this->API->post('domains', $request->toArray());
        return (new DomainModel())->fromArray($response['data']);
    }

    /**
     * Update an existing domain.
     * @param int $id The ID of the domain to update.
     * @param DomainUpdateRequest $request The data to update.
     * @return DomainModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateDomain(int $id, \Ashio\Model\DomainUpdateRequest $request): DomainModel
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
     * @param RenewDomainRequest $request The renewal data.
     * @return RenewDomainResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function renewDomain(int $id, RenewDomainRequest $request): RenewDomainResponse
    {
        $response = $this->API->post("domains/{$id}/renew", $request->toArray());
        return (new RenewDomainResponse())->fromArray($response['data']);
    }

    /**
     * Restore a domain from quarantine.
     * @param int $id The ID of the domain to restore.
     * @param DomainRestoreRequest $request
     * @return RestoreDomainResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function restoreDomain(int $id, DomainRestoreRequest $request): RestoreDomainResponse
    {
        $response = $this->API->post("domains/{$id}/restore", $request->toArray());
        return (new RestoreDomainResponse())->fromArray($response['data']);
    }

    /**
     * Approve a domain transfer.
     * @param int $id The ID of the domain transfer to approve.
     * @param DomainApproveTransferRequest $request The approval data.
     * @return \Ashio\Model\ActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function approveTransfer(int $id, DomainApproveTransferRequest $request): \Ashio\Model\ActionResponse
    {
        $response = $this->API->post("domains/{$id}/transfer/approve", $request->toArray());
        return (new \Ashio\Model\ActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Request a domain transfer.
     * @param DomainTransferRequest $request
     * @return TransferDomainResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function transferDomain(DomainTransferRequest $request): TransferDomainResponse
    {
        $response = $this->API->post("domains/transfer", $request->toArray());
        return (new TransferDomainResponse())->fromArray($response['data']);
    }

    /**
     * Request a domain trade (change of ownership).
     * @param DomainTradeRequest $request
     * @return TradeDomainResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function tradeDomain(DomainTradeRequest $request): TradeDomainResponse
    {
        $response = $this->API->post("domains/trade", $request->toArray());
        return (new TradeDomainResponse())->fromArray($response['data']);
    }

    /**
     * Suggest alternative domain names.
     * @param DomainSuggestNameRequest $request
     * @return SuggestNameDomainResult[]
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function suggestName(DomainSuggestNameRequest $request): array
    {
        $response = $this->API->post("domains/suggest-name", $request->toArray());
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
     * @param DomainSendFoa1Request $request The FOA data.
     * @return \Ashio\Model\ActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function sendFoa1(int $id, DomainSendFoa1Request $request): \Ashio\Model\ActionResponse
    {
        $response = $this->API->post("domains/{$id}/transfer/send-foa1", $request->toArray());
        return (new \Ashio\Model\ActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Get the authorization code for a domain.
     * @param int $id The ID of the domain.
     * @return \Ashio\Model\AuthCode
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Get authorization code for a domain
     * @return \Ashio\Model\AuthCode
     */
    public function getAuthCode(int $id): \Ashio\Model\AuthCode
    {
        $response = $this->API->get("domains/{$id}/authcode");
        return (new \Ashio\Model\AuthCode())->fromArray($response['data'] ?? []);
    }

    /**
     * Reset authorization code for a domain
     * @param int $id The ID of the domain.
     * @param DomainResetAuthCodeRequest $request The reset data.
     * @return \Ashio\Model\AuthCode
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function resetAuthCode(int $id, DomainResetAuthCodeRequest $request): \Ashio\Model\AuthCode
    {
        $response = $this->API->post("domains/{$id}/authcode/reset", $request->toArray());
        return (new \Ashio\Model\AuthCode())->fromArray($response['data'] ?? []);
    }

    /**
     * Retry the last failed operation for a domain.
     * @param int $id The ID of the domain.
     * @param DomainTryAgainLastOperationRequest $request The retry data.
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function tryAgainLastOperation(int $id, DomainTryAgainLastOperationRequest $request): bool
    {
        $response = $this->API->post("domains/{$id}/last-operation/restart", $request->toArray());
        return (bool)($response['data']['success'] ?? false);
    }

    /**
     * Get additional data required for a specific TLD.
     * @param array $params The parameters to get additional data.
     * @return \Ashio\Model\AdditionalDataSpecification[]
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
            $specs[] = (new \Ashio\Model\AdditionalDataSpecification())->fromArray($row);
        }
        return $specs;
    }

    /**
     * Get additional data required for a specific customer.
     * @param array $params The parameters to get customer additional data.
     * @return \Ashio\Model\AdditionalDataSpecification[]
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
            $specs[] = (new \Ashio\Model\AdditionalDataSpecification())->fromArray($row);
        }
        return $specs;
    }

    /**
     * List all TLDs.
     * @param array $params Optional parameters to filter the list of TLDs.
     * @return \Ashio\Model\PaginatedResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * List TLDs.
     * @return \Ashio\Model\PaginatedResponse
     * @phpstan-return \Ashio\Model\PaginatedResponse<Tld>
     * @psalm-return \Ashio\Model\PaginatedResponse<Tld>
     */
    public function listTlds(int $limit = 100, int $offset = 0, ?string $namePattern = null, bool $withPrice = false, bool $withDescription = false): \Ashio\Model\PaginatedResponse
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
        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $tlds);
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
