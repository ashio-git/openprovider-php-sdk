<?php

namespace Ashio\Domain;

use GuzzleHttp\Exception\GuzzleException;
use Ashio\Model\EasyDmarcOrder;
use Ashio\Model\PaginatedResponse;
use Ashio\Model\EasyDmarcCreateRequest;
use Ashio\Model\EasyDmarcRetryRequest;
use Ashio\OpenProvider;

class EasyDMARC
{
    private OpenProvider $API;
    public function __construct(OpenProvider $API)
    {
        $this->API = $API;
    }

    /**
     * Get EasyDMARC order info (current context).
     * @param array $params
     * @return EasyDmarcOrder
     * @throws GuzzleException
     */
    public function getEasyDmarc(array $params = []): EasyDmarcOrder
    {
        $response = $this->API->get('easydmarcs', $params);
        return (new EasyDmarcOrder())->fromArray($response['data'] ?? []);
    }

    /**
     * Create EasyDMARC order.
     * @param EasyDmarcCreateRequest $request
     * @return EasyDmarcOrder
     * @throws GuzzleException
     */
    public function createEasyDmarc(EasyDmarcCreateRequest $request): EasyDmarcOrder
    {
        $response = $this->API->post('easydmarcs', $request->toArray());
        return (new EasyDmarcOrder())->fromArray($response['data'] ?? []);
    }

    /**
     * List EasyDMARC orders.
     * @param int $limit
     * @param int $offset
     * @return PaginatedResponse
     * @phpstan-return PaginatedResponse<EasyDmarcOrder>
     * @psalm-return PaginatedResponse<EasyDmarcOrder>
     * @throws GuzzleException
     */
    public function listEasyDmarc(int $limit = 100, int $offset = 0): PaginatedResponse
    {
        $response = $this->API->get('easydmarcs/list', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
        $items = [];
        foreach ($response['data']['results'] ?? [] as $row) {
            $items[] = (new EasyDmarcOrder())->fromArray($row);
        }
        return new PaginatedResponse((int)($response['data']['total'] ?? 0), $items);
    }

    /**
     * Delete EasyDMARC order.
     * @param int $id
     * @return bool
     * @throws GuzzleException
     */
    public function deleteEasyDmarc(int $id): bool
    {
        $response = $this->API->delete("easydmarcs/{$id}");
        return (bool)($response['data']['success'] ?? false);
    }

    /**
     * Retry EasyDMARC order action.
     * @param int $id
     * @param EasyDmarcRetryRequest $request
     * @return EasyDmarcOrder
     * @throws GuzzleException
     */
    public function retryEasyDmarc(int $id, EasyDmarcRetryRequest $request): EasyDmarcOrder
    {
        $response = $this->API->post("easydmarcs/{$id}/retry", $request->toArray());
        return (new EasyDmarcOrder())->fromArray($response['data'] ?? []);
    }

    /**
     * Get SSO URL for EasyDMARC order.
     * @param int $id
     * @return string
     * @throws GuzzleException
     */
    public function getEasyDmarcSso(int $id): string
    {
        $response = $this->API->get("easydmarcs/{$id}/sso");
        return (string)($response['data']['url'] ?? '');
    }
}
