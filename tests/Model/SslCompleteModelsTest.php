<?php

namespace Ashio\Tests\Model;

use Ashio\Model\SslOrderCancelRequest;
use Ashio\Model\SslOrderOtpTokenRequest;
use Ashio\Model\SslOrderReissueRequest;
use Ashio\Model\SslOrderRenewRequest;
use Ashio\Model\SslOrderResendApproverEmailRequest;
use Ashio\Model\SslProductWarranty;
use PHPUnit\Framework\TestCase;

class SslCompleteModelsTest extends TestCase
{
    public function testSslOrderCancelRequestConstructor(): void
    {
        $request = new SslOrderCancelRequest();
        $request->orderId = 123;
        $request->reason = 'No longer needed';

        $this->assertEquals(123, $request->orderId);
        $this->assertEquals('No longer needed', $request->reason);
    }

    public function testSslOrderOtpTokenRequestConstructor(): void
    {
        $request = new SslOrderOtpTokenRequest();
        $request->orderId = 456;

        $this->assertEquals(456, $request->orderId);
    }

    public function testSslOrderReissueRequestConstructor(): void
    {
        $request = new SslOrderReissueRequest();
        $request->orderId = 789;

        $this->assertEquals(789, $request->orderId);
    }

    public function testSslOrderRenewRequestConstructor(): void
    {
        $request = new SslOrderRenewRequest();
        $request->orderId = 101;
        $request->years = 2;

        $this->assertEquals(101, $request->orderId);
        $this->assertEquals(2, $request->years);
    }

    public function testSslOrderResendApproverEmailRequestConstructor(): void
    {
        $request = new SslOrderResendApproverEmailRequest();
        $request->orderId = 202;
        $request->email = 'admin@example.com';

        $this->assertEquals(202, $request->orderId);
        $this->assertEquals('admin@example.com', $request->email);
    }

    public function testSslProductWarrantyConstructor(): void
    {
        $warranty = new SslProductWarranty();
        $warranty->amount = 10000.00;
        $warranty->currency = 'USD';

        $this->assertEquals(10000.00, $warranty->amount);
        $this->assertEquals('USD', $warranty->currency);
    }

    public function testEmptySslOrderCancelRequest(): void
    {
        $request = new SslOrderCancelRequest();

        $this->assertNull($request->orderId);
        $this->assertNull($request->reason);
    }
}
