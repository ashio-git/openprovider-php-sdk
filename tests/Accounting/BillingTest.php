<?php

namespace Vexura\Tests\Accounting;

use PHPUnit\Framework\TestCase;
use Vexura\OpenProvider;
use Vexura\Accounting\Billing;
use Vexura\Model\Invoice as InvoiceModel;
use Vexura\Model\Payment as PaymentModel;
use Vexura\Model\Transaction as TransactionModel;

class BillingTest extends TestCase
{
    public function testListInvoices()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['id' => 123, 'invoice_number' => 'INV-001'],
                    ]
                ]
            ]);

        $billing = new Billing($mockApi);
        $result = $billing->listInvoices();

        $this->assertInstanceOf(\Vexura\Model\PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
        $this->assertCount(1, $result->results);
        $this->assertInstanceOf(InvoiceModel::class, $result->results[0]);
        $this->assertEquals(123, $result->results[0]->id);
    }

    public function testListPayments()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['id' => 456, 'status' => 'paid'],
                    ]
                ]
            ]);

        $billing = new Billing($mockApi);
        $result = $billing->listPayments();

        $this->assertInstanceOf(\Vexura\Model\PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
        $this->assertCount(1, $result->results);
        $this->assertInstanceOf(PaymentModel::class, $result->results[0]);
        $this->assertEquals(456, $result->results[0]->id);
    }

    public function testListTransactions()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['reference_id' => 789, 'action' => 'create'],
                    ]
                ]
            ]);

        $billing = new Billing($mockApi);
        $result = $billing->listTransactions();

        $this->assertInstanceOf(\Vexura\Model\PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
        $this->assertCount(1, $result->results);
        $this->assertInstanceOf(TransactionModel::class, $result->results[0]);
        $this->assertEquals(789, $result->results[0]->reference_id);
    }

    public function testListInvoicesUnauthorized401()
    {
        $client = new \GuzzleHttp\Client(['handler' => \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode(['data' => ['token' => 'test']])),
            new \GuzzleHttp\Psr7\Response(401, [], json_encode(['desc' => 'Unauthorized'])),
        ])), 'http_errors' => false]);

        $op = new \Vexura\OpenProvider('u','p',$client);
        $billing = new Billing($op);
        $this->expectException(\Vexura\Exception\AuthenticationException::class);
        $billing->listInvoices();
    }
}
