<?php

namespace Ashio\Tests\Model;

use Ashio\Model\SslActionResponse;
use Ashio\Model\SslOrder;
use Ashio\Model\SslOrderAdditionalData;
use Ashio\Model\SslOrderDomainValidationMethods;
use Ashio\Model\SslOrderDomainValidationStatuses;
use Ashio\Model\SslOrderOptions;
use PHPUnit\Framework\TestCase;

class SslOrderModelsTest extends TestCase
{
    public function testSslActionResponseConstructor(): void
    {
        $response = (new SslActionResponse())->fromArray(['id' => '12345', 'success' => true]);

        // id is cast to int even when the API sends it as a string.
        $this->assertSame(12345, $response->id);
        $this->assertTrue($response->success);
    }

    public function testSslOrderConstructor(): void
    {
        $order = new SslOrder([
            'id' => 12345,
            'common_name' => 'example.com',
            'status' => 'ACT',
            'order_date' => '2024-01-01 10:00:00',
            'expiration_date' => '2025-01-01 10:00:00',
            'product_id' => 18,
            'product_name' => 'PositiveSSL',
            'brand_name' => 'Sectigo',
            'autorenew' => 'on',
            'host_names' => ['example.com', 'www.example.com'],
            'vendor_order_id' => 'V-998877',
            'validation_method' => 'dns',
            'administrative_handle' => 'XX123456-XX',
            'vendor_reference_id' => 'REF-1',
            'domain_validation_methods' => [
                ['host_name' => 'example.com', 'method' => 'dns'],
            ],
            'additional_data' => [
                ['dns_record' => '_abc.example.com', 'dns_value' => 'xyz.sectigo.com', 'domain' => 'example.com'],
            ],
            'unknown_key' => 'ignored',
        ]);

        $this->assertSame(12345, $order->id);
        $this->assertSame('example.com', $order->common_name);
        $this->assertSame('ACT', $order->status);
        $this->assertSame('2025-01-01 10:00:00', $order->expiration_date);
        $this->assertSame(18, $order->product_id);
        $this->assertSame('PositiveSSL', $order->product_name);
        $this->assertSame('Sectigo', $order->brand_name);
        $this->assertSame('on', $order->autorenew);
        $this->assertSame(['example.com', 'www.example.com'], $order->host_names);
        // snake_case API keys mapped onto camelCase properties
        $this->assertSame('V-998877', $order->vendorOrderId);
        $this->assertSame('dns', $order->validationMethod);
        $this->assertSame('XX123456-XX', $order->administrativeHandle);
        $this->assertSame('REF-1', $order->vendorReferenceId);

        $this->assertCount(1, $order->domain_validation_methods);
        $this->assertInstanceOf(SslOrderDomainValidationMethods::class, $order->domain_validation_methods[0]);
        $this->assertSame('dns', $order->domain_validation_methods[0]->method);

        $this->assertCount(1, $order->additional_data);
        $this->assertInstanceOf(SslOrderAdditionalData::class, $order->additional_data[0]);
        $this->assertSame('_abc.example.com', $order->additional_data[0]->dns_record);
        $this->assertSame('xyz.sectigo.com', $order->additional_data[0]->dns_value);

        $this->assertFalse(property_exists($order, 'unknown_key'));
    }

    public function testSslOrderOptionsConstructor(): void
    {
        $options = new SslOrderOptions(features: 'wildcard');

        $this->assertSame('wildcard', $options->features);
        $this->assertSame(['features' => 'wildcard'], $options->toArray());
        $this->assertEquals($options, SslOrderOptions::fromArray(['features' => 'wildcard']));
        $this->assertSame([], (new SslOrderOptions())->toArray());
    }

    public function testSslOrderDomainValidationMethodsConstructor(): void
    {
        $methods = new SslOrderDomainValidationMethods(host_name: 'www.example.com', method: 'email');

        $this->assertSame('www.example.com', $methods->host_name);
        $this->assertSame('email', $methods->method);

        $data = ['host_name' => 'www.example.com', 'method' => 'email'];
        $this->assertSame($data, $methods->toArray());
        $this->assertEquals($methods, SslOrderDomainValidationMethods::fromArray($data));
    }

    public function testSslOrderDomainValidationStatusesConstructor(): void
    {
        $data = [
            'ca_operation' => 'issue',
            'ca_order_status' => 'pending',
            'ca_status' => 'awaiting_validation',
            'status' => 'REQ',
        ];

        $statuses = SslOrderDomainValidationStatuses::fromArray($data);

        $this->assertSame('issue', $statuses->ca_operation);
        $this->assertSame('pending', $statuses->ca_order_status);
        $this->assertSame('awaiting_validation', $statuses->ca_status);
        $this->assertSame('REQ', $statuses->status);
        $this->assertSame($data, $statuses->toArray());
    }

    public function testEmptySslOrder(): void
    {
        $order = new SslOrder();

        $this->assertNull($order->id);
        $this->assertNull($order->status);
        $this->assertNull($order->options);
        $this->assertNull($order->domain_validation_statuses);
    }

    public function testEmptySslActionResponse(): void
    {
        $response = (new SslActionResponse())->fromArray([]);

        $this->assertNull($response->id);
        $this->assertNull($response->success);
    }
}
