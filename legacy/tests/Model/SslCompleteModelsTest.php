<?php

namespace Ashio\Tests\Model;

use Ashio\Model\SslOrderCancelRequest;
use Ashio\Model\SslOrderOtpTokenRequest;
use Ashio\Model\SslOrderReissueRequest;
use Ashio\Model\SslOrderRenewRequest;
use Ashio\Model\SslOrderResendApproverEmailRequest;
use Ashio\Model\SslProductPrice;
use Ashio\Model\SslProductReseller;
use Ashio\Model\SslProductWarranty;
use PHPUnit\Framework\TestCase;

class SslCompleteModelsTest extends TestCase
{
    public function testSslOrderCancelRequestConstructor(): void
    {
        // The order id is a path parameter (SSL::cancelOrder(int $id, ...)); the body is empty.
        $request = new SslOrderCancelRequest();

        $this->assertSame([], $request->toArray());
    }

    public function testSslOrderOtpTokenRequestConstructor(): void
    {
        // The order id is a path parameter (SSL::createOtpToken(int $id, ...)); the body is empty.
        $request = new SslOrderOtpTokenRequest();

        $this->assertSame([], get_object_vars($request));
        $this->assertSame([], $request->toArray());
    }

    public function testSslOrderReissueRequestConstructor(): void
    {
        $request = new SslOrderReissueRequest();
        $request->approver_email = 'admin@example.com';
        $request->csr = "-----BEGIN CERTIFICATE REQUEST-----\nMIIC\n-----END CERTIFICATE REQUEST-----";
        $request->domain_validation_methods = [['host_name' => 'example.com', 'method' => 'dns']];
        $request->enable_dns_automation = false;
        $request->host_names = ['example.com', 'www.example.com'];
        $request->organization_handle = 'XX123456-XX';
        $request->signature_hash_algorithm = 'sha2';
        $request->software_id = 'linux';

        $this->assertSame([
            'approver_email' => 'admin@example.com',
            'csr' => "-----BEGIN CERTIFICATE REQUEST-----\nMIIC\n-----END CERTIFICATE REQUEST-----",
            'domain_validation_methods' => [['host_name' => 'example.com', 'method' => 'dns']],
            'enable_dns_automation' => false,
            'host_names' => ['example.com', 'www.example.com'],
            'organization_handle' => 'XX123456-XX',
            'signature_hash_algorithm' => 'sha2',
            'software_id' => 'linux',
        ], $request->toArray());

        $partial = new SslOrderReissueRequest();
        $partial->csr = 'CSR';
        $this->assertSame(['csr' => 'CSR'], $partial->toArray());
    }

    public function testSslOrderRenewRequestConstructor(): void
    {
        $request = new SslOrderRenewRequest();
        $this->assertSame([], $request->toArray());

        $request->enable_dns_automation = true;
        $this->assertSame(['enable_dns_automation' => true], $request->toArray());

        $request->enable_dns_automation = false;
        $this->assertSame(['enable_dns_automation' => false], $request->toArray());
    }

    public function testSslOrderResendApproverEmailRequestConstructor(): void
    {
        // The order id is a path parameter (SSL::resendApproverEmail(int $id, ...)); the body is empty.
        $request = new SslOrderResendApproverEmailRequest();

        $this->assertSame([], get_object_vars($request));
        $this->assertSame([], $request->toArray());
    }

    public function testSslProductWarrantyConstructor(): void
    {
        $warranty = new SslProductWarranty(
            product: new SslProductPrice(currency: 'USD', price: 10000.0),
            reseller: new SslProductReseller(currency: 'EUR', price: 9200.0),
        );

        $this->assertSame('USD', $warranty->product->currency);
        $this->assertSame(10000.0, $warranty->product->price);
        $this->assertSame('EUR', $warranty->reseller->currency);
        $this->assertSame(9200.0, $warranty->reseller->price);

        $data = [
            'product' => ['currency' => 'USD', 'price' => 10000.0],
            'reseller' => ['currency' => 'EUR', 'price' => 9200.0],
        ];
        $this->assertSame($data, $warranty->toArray());

        $parsed = SslProductWarranty::fromArray($data);
        $this->assertInstanceOf(SslProductPrice::class, $parsed->product);
        $this->assertInstanceOf(SslProductReseller::class, $parsed->reseller);
        $this->assertEquals($warranty, $parsed);
    }

    public function testEmptySslOrderCancelRequest(): void
    {
        $request = new SslOrderCancelRequest();

        $this->assertSame([], get_object_vars($request));
    }
}
