<?php

namespace Ashio\Tests\SSL;

use Ashio\Exception\ValidationException;
use Ashio\Model\CsrCreateResponse;
use Ashio\Model\CsrDecodeResponse;
use Ashio\Model\PaginatedResponse;
use Ashio\Model\SslActionResponse;
use Ashio\OpenProvider;
use Ashio\SSL\SSL;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class SslTest extends TestCase
{
    private function makeClient(array $responses): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);

        return new Client(['handler' => $handlerStack, 'http_errors' => false]);
    }

    private function loginOk(): Response
    {
        return new Response(200, [], json_encode(['data' => ['token' => 'test']])) ;
    }

    public function testCreateAndDecodeCsr(): void
    {
        $createPayload = ['data' => ['command' => 'openssl req', 'csr' => '---CSR---']];
        $decodePayload = ['data' => ['domain_names_count' => 1, 'subject' => ['common_name' => 'example.com']]];

        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($createPayload)),
            new Response(200, [], json_encode($decodePayload)),
        ]);

        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);

        $create = $ssl->createCsr(new \Ashio\Model\CsrCreateRequest('example.com'));
        $this->assertInstanceOf(CsrCreateResponse::class, $create);
        $this->assertSame('openssl req', $create->command);

        $decode = $ssl->decodeCsr(new \Ashio\Model\CsrDecodeRequest('---CSR---'));
        $this->assertInstanceOf(CsrDecodeResponse::class, $decode);
        $this->assertSame(1, $decode->domain_names_count);
        $this->assertSame('example.com', $decode->subject->common_name);
    }

    public function testUpdateOrderReturnsActionResponse(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode(['data' => ['id' => 123, 'success' => true]])),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $req = new \Ashio\Model\SslOrderUpdateRequest();
        $req->autorenew = 'off';
        $resp = $ssl->updateOrder(123, $req);
        $this->assertInstanceOf(SslActionResponse::class, $resp);
        $this->assertSame(123, $resp->id);
        $this->assertTrue($resp->success);
    }

    public function testCreateOrderWithRequestDto(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode(['data' => ['id' => 9, 'product_id' => 1, 'status' => 'new']])),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $req = new \Ashio\Model\SslOrderCreateRequest(1, 'CSR', ['example.com'], 12);
        $req->approver_email = 'admin@example.com';
        $order = $ssl->createOrder($req);
        $this->assertInstanceOf(\Ashio\Model\SslOrder::class, $order);
        $this->assertSame(1, $order->product_id);
    }

    public function testListProductsReturnsPaginated(): void
    {
        $payload = ['data' => ['total' => 1, 'results' => [['id' => 1, 'name' => 'SSL DV']]]];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $resp = $ssl->listProducts(limit: 1);
        $this->assertInstanceOf(PaginatedResponse::class, $resp);
        $this->assertSame(1, $resp->total);
    }

    public function testCreateCsrValidationError(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(422, [], json_encode(['desc' => 'Invalid'])),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $this->expectException(ValidationException::class);
        $ssl->createCsr(new \Ashio\Model\CsrCreateRequest('example.com'));
    }

    public function testGetProductUnauthorized401(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(401, [], json_encode(['desc' => 'Unauthorized'])),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $this->expectException(\Ashio\Exception\AuthenticationException::class);
        $ssl->getProduct(1);
    }

    public function testListOrders(): void
    {
        $payload = ['data' => ['total' => 1, 'results' => [['id' => 1, 'status' => 'active']]]];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $result = $ssl->listOrders();
        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
    }

    public function testGetOrder(): void
    {
        $payload = ['data' => ['id' => 123, 'status' => 'active']];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $result = $ssl->getOrder(123);
        $this->assertInstanceOf(\Ashio\Model\SslOrder::class, $result);
    }

    public function testGetProduct(): void
    {
        $payload = ['data' => ['id' => 1, 'name' => 'SSL DV']];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $result = $ssl->getProduct(1);
        $this->assertInstanceOf(\Ashio\Model\SslProduct::class, $result);
    }

    public function testCancelOrder(): void
    {
        $payload = ['data' => ['success' => true]];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $req = new \Ashio\Model\SslOrderCancelRequest();
        $result = $ssl->cancelOrder(123, $req);
        $this->assertInstanceOf(SslActionResponse::class, $result);
    }

    public function testReissueOrder(): void
    {
        $payload = ['data' => ['id' => 123, 'success' => true]];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $req = new \Ashio\Model\SslOrderReissueRequest();
        $result = $ssl->reissueOrder(123, $req);
        $this->assertInstanceOf(SslActionResponse::class, $result);
    }

    public function testRenewOrder(): void
    {
        $payload = ['data' => ['id' => 123, 'success' => true]];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $req = new \Ashio\Model\SslOrderRenewRequest();
        $result = $ssl->renewOrder(123, $req);
        $this->assertInstanceOf(SslActionResponse::class, $result);
    }

    public function testUpdateApproverEmailAddress(): void
    {
        $payload = ['data' => ['success' => true]];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $req = new \Ashio\Model\ApproverEmailUpdateRequest('admin@example.com');
        $result = $ssl->updateApproverEmailAddress(123, $req);
        $this->assertInstanceOf(SslActionResponse::class, $result);
    }

    public function testResendApproverEmail(): void
    {
        $payload = ['data' => ['success' => true]];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $req = new \Ashio\Model\SslOrderResendApproverEmailRequest();
        $result = $ssl->resendApproverEmail(123, $req);
        $this->assertInstanceOf(SslActionResponse::class, $result);
    }

    public function testCreateOtpToken(): void
    {
        $payload = ['data' => ['success' => true]];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $req = new \Ashio\Model\SslOrderOtpTokenRequest();
        $result = $ssl->createOtpToken(123, $req);
        $this->assertInstanceOf(SslActionResponse::class, $result);
    }

    public function testListApproverEmails(): void
    {
        $payload = ['data' => ['results' => ['admin@example.com', 'webmaster@example.com']]];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u', 'p', httpClient: $client);
        $ssl = new SSL($api);
        $result = $ssl->listApproverEmails(['domain' => 'example.com']);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }
}
