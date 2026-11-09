<?php

namespace Ashio\Tests\Model;

use Ashio\Model\SslOrderAdditionalData;
use Ashio\Model\SslProductReseller;
use PHPUnit\Framework\TestCase;

class MoreSslModelsTest extends TestCase
{
    public function testSslProductResellerFromArray(): void
    {
        $data = [
            'currency' => 'USD',
            'price' => 99.99,
        ];

        $product = (new SslProductReseller())->fromArray($data);

        $this->assertEquals('USD', $product->currency);
        $this->assertEquals(99.99, $product->price);
    }

    public function testSslOrderAdditionalDataFromArray(): void
    {
        $data = [
            'domain' => 'example.com',
            'dns' => 'ns1.example.com',
            'file_name' => 'verification.txt',
        ];

        $additionalData = (new SslOrderAdditionalData())->fromArray($data);

        $this->assertEquals('example.com', $additionalData->domain);
        $this->assertEquals('ns1.example.com', $additionalData->dns);
        $this->assertEquals('verification.txt', $additionalData->file_name);
    }

    public function testEmptySslProductReseller(): void
    {
        $product = new SslProductReseller();

        $this->assertNull($product->currency);
        $this->assertNull($product->price);
    }

    public function testEmptySslOrderAdditionalData(): void
    {
        $data = new SslOrderAdditionalData();

        $this->assertNull($data->domain);
        $this->assertNull($data->dns);
        $this->assertNull($data->file_name);
    }
}
