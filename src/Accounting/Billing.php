<?php

namespace Vexura\Accounting;

use GuzzleHttp\Exception\GuzzleException;
use Vexura\Exception\ApiException;
use Vexura\Exception\AuthenticationException;
use Vexura\Exception\NotFoundException;
use Vexura\Exception\ValidationException;
use Vexura\OpenProvider;
use Vexura\Model\Invoice as InvoiceModel;
use Vexura\Model\Payment as PaymentModel;
use Vexura\Model\Transaction as TransactionModel;

class Billing
{
    private OpenProvider $API;

    public function __construct(OpenProvider $API)
    {
        $this->API = $API;
    }

    /**
     * List all invoices with optional filters.
     * @param int $limit
     * @param int $offset
     * @param string|null $order
     * @param string|null $startCreationDate
     * @param string|null $endCreationDate
     * @return array
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Vexura\Model\PaginatedResponse
     * @phpstan-return \Vexura\Model\PaginatedResponse<InvoiceModel>
     * @psalm-return \Vexura\Model\PaginatedResponse<InvoiceModel>
     */
    public function listInvoices(int $limit = 100, int $offset = 0, ?string $order = null, ?string $startCreationDate = null, ?string $endCreationDate = null): \Vexura\Model\PaginatedResponse
    {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'order' => $order,
            'start_creation_date' => $startCreationDate,
            'end_creation_date' => $endCreationDate,
        ]);

        $response = $this->API->get('invoices', $params);
        $invoices = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $invoiceData) {
                $invoices[] = new InvoiceModel($invoiceData);
            }
        }
        return new \Vexura\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $invoices);
    }

    /**
     * List all payments with optional filters.
     * @param int $limit
     * @param int $offset
     * @param string|null $order
     * @param string|null $status
     * @return array
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Vexura\Model\PaginatedResponse
     * @phpstan-return \Vexura\Model\PaginatedResponse<PaymentModel>
     * @psalm-return \Vexura\Model\PaginatedResponse<PaymentModel>
     */
    public function listPayments(int $limit = 100, int $offset = 0, ?string $order = null, ?string $status = null): \Vexura\Model\PaginatedResponse
    {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'order' => $order,
            'status' => $status,
        ]);

        $response = $this->API->get('payments', $params);
        $payments = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $paymentData) {
                $payments[] = new PaymentModel($paymentData);
            }
        }
        return new \Vexura\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $payments);
    }

    /**
     * List all transactions with optional filters.
     * @param int $limit
     * @param int $offset
     * @param string|null $order
     * @return array
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Vexura\Model\PaginatedResponse
     * @phpstan-return \Vexura\Model\PaginatedResponse<TransactionModel>
     * @psalm-return \Vexura\Model\PaginatedResponse<TransactionModel>
     */
    public function listTransactions(int $limit = 100, int $offset = 0, ?string $order = null): \Vexura\Model\PaginatedResponse
    {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'order' => $order,
        ]);

        $response = $this->API->get('transactions', $params);
        $transactions = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $transactionData) {
                $transactions[] = new TransactionModel($transactionData);
            }
        }
        return new \Vexura\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $transactions);
    }
}
