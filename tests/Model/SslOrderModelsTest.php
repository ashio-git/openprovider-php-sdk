<?php

namespace Ashio\Tests\Model;

use Ashio\Model\SslActionResponse;
use Ashio\Model\SslOrder;
use Ashio\Model\SslOrderOptions;
use Ashio\Model\SslOrderDomainValidationMethods;
use Ashio\Model\SslOrderDomainValidationStatuses;
use PHPUnit\Framework\TestCase;

class SslOrderModelsTest extends TestCase
{
    public function testSslActionResponseConstructor(): void
    {
        $response = new SslActionResponse();
        $response->success = true;
        $response->message = 'Order created';

        $this->assertTrue($response->success);
        $this->assertEquals('Order created', $response->message);
    }

    public function testSslOrderConstructor(): void
    {
        $order = new SslOrder();
        $order->id = 12345;
        $order->status = 'active';

        $this->assertEquals(12345, $order->id);
        $this->assertEquals('active', $order->status);
    }

    public function testSslOrderOptionsConstructor(): void
    {
        $options = new SslOrderOptions();
        $options->autoRenew = true;

        $this->assertTrue($options->autoRenew);
    }

    public function testSslOrderDomainValidationMethodsConstructor(): void
    {
        $methods = new SslOrderDomainValidationMethods();
        $methods->dns = true;
        $methods->email = false;

        $this->assertTrue($methods->dns);
        $this->assertFalse($methods->email);
    }

    public function testSslOrderDomainValidationStatusesConstructor(): void
    {
        $statuses = new SslOrderDomainValidationStatuses();
        $statuses->validated = true;

        $this->assertTrue($statuses->validated);
    }

    public function testEmptySslOrder(): void
    {
        $order = new SslOrder();

        $this->assertNull($order->id);
        $this->assertNull($order->status);
    }

    public function testEmptySslActionResponse(): void
    {
        $response = new SslActionResponse();

        $this->assertNull($response->success);
        $this->assertNull($response->message);
    }
}
