<?php

namespace Ashio\Tests\Model;

use Ashio\Model\SslProduct;
use Ashio\Model\SslProductPrice;
use Ashio\Model\SslProductPrices;
use Ashio\Model\SslProductPriceGroup;
use Ashio\Model\SslProductLevelPrices;
use Ashio\Model\SslProductSupportedSoftware;
use PHPUnit\Framework\TestCase;

class SslProductModelsTest extends TestCase
{
    public function testSslProductConstructor(): void
    {
        $product = new SslProduct();
        $product->id = 100;
        $product->name = 'SSL Certificate';

        $this->assertEquals(100, $product->id);
        $this->assertEquals('SSL Certificate', $product->name);
    }

    public function testSslProductPriceConstructor(): void
    {
        $price = new SslProductPrice();
        $price->price = 99.99;
        $price->currency = 'USD';

        $this->assertEquals(99.99, $price->price);
        $this->assertEquals('USD', $price->currency);
    }

    public function testSslProductPricesConstructor(): void
    {
        $prices = new SslProductPrices();
        $prices->create = 99.99;
        $prices->renew = 89.99;

        $this->assertEquals(99.99, $prices->create);
        $this->assertEquals(89.99, $prices->renew);
    }

    public function testSslProductPriceGroupConstructor(): void
    {
        $priceGroup = new SslProductPriceGroup();
        $priceGroup->currency = 'EUR';

        $this->assertEquals('EUR', $priceGroup->currency);
    }

    public function testSslProductLevelPricesConstructor(): void
    {
        $levelPrices = new SslProductLevelPrices();
        $levelPrices->level = 1;

        $this->assertEquals(1, $levelPrices->level);
    }

    public function testSslProductSupportedSoftwareConstructor(): void
    {
        $software = new SslProductSupportedSoftware();
        $software->name = 'Apache';

        $this->assertEquals('Apache', $software->name);
    }

    public function testEmptySslProduct(): void
    {
        $product = new SslProduct();

        $this->assertNull($product->id);
        $this->assertNull($product->name);
    }

    public function testEmptySslProductPrice(): void
    {
        $price = new SslProductPrice();

        $this->assertNull($price->price);
        $this->assertNull($price->currency);
    }
}
