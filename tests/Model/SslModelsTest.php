<?php

namespace Ashio\Tests\Model;

use Ashio\Model\CsrCreateRequest;
use Ashio\Model\CsrDecodeRequest;
use Ashio\Model\SslOrder;
use Ashio\Model\SslOrderCreateRequest;
use Ashio\Model\SslOrderUpdateRequest;
use Ashio\Model\SslProduct;
use PHPUnit\Framework\TestCase;

class SslModelsTest extends TestCase
{
    public function testSslOrderFromArray()
    {
        $data = [
            'id' => 123,
            'product_id' => 1,
            'status' => 'active',
            'common_name' => 'example.com',
        ];

        $order = (new SslOrder())->fromArray($data);

        $this->assertEquals(123, $order->id);
        $this->assertEquals(1, $order->product_id);
        $this->assertEquals('active', $order->status);
        $this->assertEquals('example.com', $order->common_name);
    }

    public function testSslOrderConstructorWithData()
    {
        $data = [
            'id' => 456,
            'product_id' => 2,
        ];

        $order = new SslOrder($data);

        $this->assertEquals(456, $order->id);
        $this->assertEquals(2, $order->product_id);
    }

    public function testSslProductFromArray()
    {
        $data = [
            'id' => 1,
            'name' => 'SSL DV',
            'brand_name' => 'Sectigo',
            'category' => 'dv',
        ];

        $product = (new SslProduct())->fromArray($data);

        $this->assertEquals(1, $product->id);
        $this->assertEquals('SSL DV', $product->name);
        $this->assertEquals('Sectigo', $product->brand_name);
    }

    public function testSslOrderCreateRequest()
    {
        $request = new SslOrderCreateRequest(1, 'CSR_CONTENT', ['example.com'], 12);

        $array = $request->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(1, $array['product_id']);
        $this->assertEquals('CSR_CONTENT', $array['csr']);
        $this->assertIsArray($array['host_names']);
        $this->assertEquals(12, $array['period']);
    }

    public function testSslOrderUpdateRequest()
    {
        $request = new SslOrderUpdateRequest();
        $request->autorenew = 'off';

        $array = $request->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('off', $array['autorenew']);
    }

    public function testCsrCreateRequest()
    {
        $request = new CsrCreateRequest('example.com');

        $array = $request->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('example.com', $array['common_name']);
    }

    public function testCsrDecodeRequest()
    {
        $request = new CsrDecodeRequest('CSR_CONTENT_HERE');

        $array = $request->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('CSR_CONTENT_HERE', $array['csr']);
    }

    public function testSslOrderWithAllFields()
    {
        $data = [
            'id' => 789,
            'product_id' => 3,
            'status' => 'pending',
            'common_name' => 'secure.example.com',
        ];

        $order = (new SslOrder())->fromArray($data);

        $this->assertEquals(789, $order->id);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals('secure.example.com', $order->common_name);
    }

    public function testSslOrderCreateRequestWithOptionalFields()
    {
        $request = new SslOrderCreateRequest(1, 'CSR', ['example.com'], 12);
        $request->approver_email = 'admin@example.com';

        $array = $request->toArray();

        $this->assertEquals('admin@example.com', $array['approver_email']);
    }

    public function testEmptySslOrder()
    {
        $order = new SslOrder();

        $this->assertNull($order->id);
        $this->assertNull($order->product_id);
        $this->assertNull($order->status);
    }

    public function testEmptySslProduct()
    {
        $product = new SslProduct();

        $this->assertNull($product->id);
        $this->assertNull($product->name);
    }
}
