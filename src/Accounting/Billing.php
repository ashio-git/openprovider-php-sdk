<?php

namespace Ashio\Accounting;

use Ashio\Exception\ApiException;
use Ashio\Exception\AuthenticationException;
use Ashio\Exception\NotFoundException;
use Ashio\Exception\ValidationException;
use Ashio\Model\Invoice as InvoiceModel;
use Ashio\Model\Payment as PaymentModel;
use Ashio\Model\Transaction as TransactionModel;
use Ashio\OpenProvider;
use GuzzleHttp\Exception\GuzzleException;

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
     * @return \Ashio\Model\PaginatedResponse
     * @phpstan-return \Ashio\Model\PaginatedResponse<InvoiceModel>
     * @psalm-return \Ashio\Model\PaginatedResponse<InvoiceModel>
     */
    public function listInvoices(int $limit = 100, int $offset = 0, ?string $order = null, ?string $startCreationDate = null, ?string $endCreationDate = null): \Ashio\Model\PaginatedResponse
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

        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $invoices);
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
     * @return \Ashio\Model\PaginatedResponse
     * @phpstan-return \Ashio\Model\PaginatedResponse<PaymentModel>
     * @psalm-return \Ashio\Model\PaginatedResponse<PaymentModel>
     */
    public function listPayments(int $limit = 100, int $offset = 0, ?string $order = null, ?string $status = null): \Ashio\Model\PaginatedResponse
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

        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $payments);
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
     * @return \Ashio\Model\PaginatedResponse
     * @phpstan-return \Ashio\Model\PaginatedResponse<TransactionModel>
     * @psalm-return \Ashio\Model\PaginatedResponse<TransactionModel>
     */
    public function listTransactions(int $limit = 100, int $offset = 0, ?string $order = null): \Ashio\Model\PaginatedResponse
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

        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $transactions);
    }
}
