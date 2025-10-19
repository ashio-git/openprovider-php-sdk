<?php

namespace Ashio\SSL;

use Ashio\Exception\ApiException;
use Ashio\Exception\AuthenticationException;
use Ashio\Exception\NotFoundException;
use Ashio\Exception\ValidationException;
use Ashio\Model\CsrCreateResponse;
use Ashio\Model\CsrDecodeResponse;
use Ashio\Model\PaginatedResponse;
use Ashio\Model\SslOrder as SslOrderModel;
use Ashio\Model\SslProduct as SslProductModel;
use Ashio\OpenProvider;
use GuzzleHttp\Exception\GuzzleException;

class SSL
{
    private OpenProvider $API;

    public function __construct(OpenProvider $API)
    {
        $this->API = $API;
    }

    /**
     * List approver emails for a domain.
     * @param array<string, mixed> $queryParams
     * @return array<int, string>
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function listApproverEmails(array $queryParams): array
    {
        $response = $this->API->get('ssl/approver-emails', $queryParams);

        return $response['data']['results'] ?? [];
    }

    /**
     * Create a new Certificate Signing Request (CSR).
     *
     * @param \Ashio\Model\CsrCreateRequest $request
     * @return \Ashio\Model\CsrCreateResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createCsr(\Ashio\Model\CsrCreateRequest $request): CsrCreateResponse
    {
        $response = $this->API->post('ssl/csr', $request->toArray());

        return (new CsrCreateResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Decode a Certificate Signing Request (CSR).
     *
     * @param \Ashio\Model\CsrDecodeRequest $request
     * @return \Ashio\Model\CsrDecodeResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function decodeCsr(\Ashio\Model\CsrDecodeRequest $request): CsrDecodeResponse
    {
        $response = $this->API->post('ssl/csr/decode', $request->toArray());

        return (new CsrDecodeResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * List all SSL orders with optional filters.
     * @param int $limit
     * @param int $offset
     * @param string|null $orderByCommonName
     * @param string|null $orderByExpirationDate
     * @param string|null $status
     * @return PaginatedResponse<SslOrderModel>
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function listOrders(
        int $limit = 100,
        int $offset = 0,
        ?string $orderByCommonName = null,
        ?string $orderByExpirationDate = null,
        ?string $status = null
    ): PaginatedResponse {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'order_by.common_name' => $orderByCommonName,
            'order_by.expiration_date' => $orderByExpirationDate,
            'status' => $status,
        ], static fn ($v) => $v !== null);

        $response = $this->API->get('ssl/orders', $params);
        $orders = [];
        foreach ($response['data']['results'] ?? [] as $orderData) {
            $orders[] = new SslOrderModel($orderData);
        }

        return new PaginatedResponse((int)($response['data']['total'] ?? 0), $orders);
    }

    /**
     * Create a new SSL order.
     * @param \Ashio\Model\SslOrderCreateRequest $request
     * @return SslOrderModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createOrder(\Ashio\Model\SslOrderCreateRequest $request): SslOrderModel
    {
        $response = $this->API->post('ssl/orders', $request->toArray());

        return new SslOrderModel($response['data'] ?? []);
    }

    /**
     * Get a specific SSL order by its ID.
     * @param int $id The ID of the SSL order.
     * @return SslOrderModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getOrder(int $id): SslOrderModel
    {
        $response = $this->API->get("ssl/orders/{$id}");

        return new SslOrderModel($response['data'] ?? []);
    }

    /**
     * Update an existing SSL order.
     * @param int $id The ID of the SSL order to update.
     * @param array $body The data to update.
     * @return \Ashio\Model\SslActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Update an existing SSL order.
     * @param int $id
     * @param \Ashio\Model\SslOrderUpdateRequest $request
     * @return \Ashio\Model\SslActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateOrder(int $id, \Ashio\Model\SslOrderUpdateRequest $request): \Ashio\Model\SslActionResponse
    {
        $response = $this->API->put("ssl/orders/{$id}", $request->toArray());

        return (new \Ashio\Model\SslActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Update the approver email address for an SSL order.
     * @param int $id The ID of the SSL order.
     * @param array $body The data for the email update.
     * @return \Ashio\Model\SslActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Update approver email address.
     * @return \Ashio\Model\SslActionResponse
     */
    public function updateApproverEmailAddress(int $id, \Ashio\Model\ApproverEmailUpdateRequest $request): \Ashio\Model\SslActionResponse
    {
        $response = $this->API->put("ssl/orders/{$id}/approver-email", $request->toArray());

        return (new \Ashio\Model\SslActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Resend the approver email for an SSL order.
     * @param int $id The ID of the SSL order.
     * @param array $body The request body.
     * @return \Ashio\Model\SslActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Resend approver email.
     * @return \Ashio\Model\SslActionResponse
     */
    public function resendApproverEmail(int $id, \Ashio\Model\SslOrderResendApproverEmailRequest $request): \Ashio\Model\SslActionResponse
    {
        $response = $this->API->post("ssl/orders/{$id}/approver-email/resend", $request->toArray());

        return (new \Ashio\Model\SslActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Cancel an SSL order.
     * @param int $id The ID of the SSL order to cancel.
     * @param array $body The cancellation data.
     * @return \Ashio\Model\SslActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Cancel SSL order.
     * @return \Ashio\Model\SslActionResponse
     */
    public function cancelOrder(int $id, \Ashio\Model\SslOrderCancelRequest $request): \Ashio\Model\SslActionResponse
    {
        $response = $this->API->post("ssl/orders/{$id}/cancel", $request->toArray());

        return (new \Ashio\Model\SslActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Create a one-time password (OTP) token for an SSL order.
     * @param int $id The ID of the SSL order.
     * @param array $body The request body.
     * @return \Ashio\Model\SslActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Create OTP token for order.
     * @return \Ashio\Model\SslActionResponse
     */
    public function createOtpToken(int $id, \Ashio\Model\SslOrderOtpTokenRequest $request): \Ashio\Model\SslActionResponse
    {
        $response = $this->API->post("ssl/orders/{$id}/otp-tokens", $request->toArray());

        return (new \Ashio\Model\SslActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Reissue an SSL order.
     * @param int $id The ID of the SSL order to reissue.
     * @param array $body The reissue data.
     * @return \Ashio\Model\SslActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Reissue SSL order.
     * @return \Ashio\Model\SslActionResponse
     */
    public function reissueOrder(int $id, \Ashio\Model\SslOrderReissueRequest $request): \Ashio\Model\SslActionResponse
    {
        $response = $this->API->post("ssl/orders/{$id}/reissue", $request->toArray());

        return (new \Ashio\Model\SslActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Renew an SSL order.
     * @param int $id The ID of the SSL order to renew.
     * @param array $body The renewal data.
     * @return \Ashio\Model\SslActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * Renew SSL order.
     * @return \Ashio\Model\SslActionResponse
     */
    public function renewOrder(int $id, \Ashio\Model\SslOrderRenewRequest $request): \Ashio\Model\SslActionResponse
    {
        $response = $this->API->post("ssl/orders/{$id}/renew", $request->toArray());

        return (new \Ashio\Model\SslActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * List all SSL products with optional filters.
     * @param int $limit
     * @param int $offset
     * @param bool|null $withPrice
     * @param bool|null $withSupportedSoftware
     * @param bool|null $withDescription
     * @return PaginatedResponse<SslProductModel>
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function listProducts(
        int $limit = 100,
        int $offset = 0,
        ?bool $withPrice = null,
        ?bool $withSupportedSoftware = null,
        ?bool $withDescription = null
    ): PaginatedResponse {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'with_price' => $withPrice,
            'with_supported_software' => $withSupportedSoftware,
            'with_description' => $withDescription,
        ], static fn ($v) => $v !== null);
        $response = $this->API->get('ssl/products', $params);
        $products = [];
        foreach ($response['data']['results'] ?? [] as $row) {
            $products[] = (new SslProductModel())->fromArray($row);
        }

        return new PaginatedResponse((int)($response['data']['total'] ?? 0), $products);
    }

    /**
     * Get a specific SSL product by its ID.
     * @param int $id The ID of the SSL product.
     * @return \Ashio\Model\SslProduct
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getProduct(int $id): SslProductModel
    {
        $response = $this->API->get("ssl/products/{$id}");

        return (new SslProductModel())->fromArray($response['data'] ?? []);
    }
}
