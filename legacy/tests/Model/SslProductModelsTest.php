<?php

namespace Ashio\Tests\Model;

use Ashio\Model\SslProduct;
use Ashio\Model\SslProductLevelPrices;
use Ashio\Model\SslProductPrice;
use Ashio\Model\SslProductPriceGroup;
use Ashio\Model\SslProductPrices;
use Ashio\Model\SslProductReseller;
use Ashio\Model\SslProductSupportedSoftware;
use Ashio\Model\SslProductWarranty;
use PHPUnit\Framework\TestCase;

class SslProductModelsTest extends TestCase
{
    public function testSslProductConstructor(): void
    {
        $product = (new SslProduct())->fromArray([
            'id' => 100,
            'name' => 'PositiveSSL Wildcard',
            'brand_name' => 'Sectigo',
            'bypass_san' => false,
            'category' => 'domain_validation',
            'validation_method' => 'domain',
            'encryption' => '256 bit',
            'included_domains_count' => 1,
            'is_wildcard_supported' => true,
            'max_period' => 2,
            'prices' => [
                ['period' => 1, 'price' => ['product' => ['currency' => 'EUR', 'price' => 89.0]]],
            ],
            'level_prices' => [
                ['level' => 1, 'name' => 'Tier 1', 'prices' => [['period' => 1]]],
            ],
            'supported_software' => [
                ['id' => 'linux', 'title' => 'Apache + OpenSSL'],
            ],
            'warranty' => ['product' => ['currency' => 'USD', 'price' => 50000.0]],
        ]);

        $this->assertSame(100, $product->id);
        $this->assertSame('PositiveSSL Wildcard', $product->name);
        $this->assertSame('Sectigo', $product->brand_name);
        $this->assertFalse($product->bypass_san);
        $this->assertSame('domain_validation', $product->category);
        $this->assertSame('domain', $product->validation_method);
        $this->assertSame('256 bit', $product->encryption);
        $this->assertSame(1, $product->included_domains_count);
        $this->assertTrue($product->is_wildcard_supported);
        $this->assertSame(2, $product->max_period);

        $this->assertCount(1, $product->prices);
        $this->assertInstanceOf(SslProductPrices::class, $product->prices[0]);
        $this->assertSame(1, $product->prices[0]->period);
        $this->assertSame(89.0, $product->prices[0]->price->product->price);

        $this->assertCount(1, $product->level_prices);
        $this->assertInstanceOf(SslProductLevelPrices::class, $product->level_prices[0]);
        $this->assertSame('Tier 1', $product->level_prices[0]->name);

        $this->assertCount(1, $product->supported_software);
        $this->assertInstanceOf(SslProductSupportedSoftware::class, $product->supported_software[0]);
        $this->assertSame('Apache + OpenSSL', $product->supported_software[0]->title);

        $this->assertInstanceOf(SslProductWarranty::class, $product->warranty);
        $this->assertSame(50000.0, $product->warranty->product->price);
        $this->assertNull($product->warranty->reseller);
    }

    public function testSslProductPriceConstructor(): void
    {
        $price = new SslProductPrice(currency: 'USD', price: 99.99);

        $this->assertSame(99.99, $price->price);
        $this->assertSame('USD', $price->currency);
        $this->assertSame(['currency' => 'USD', 'price' => 99.99], $price->toArray());
        $this->assertEquals($price, SslProductPrice::fromArray(['currency' => 'USD', 'price' => 99.99]));
    }

    public function testSslProductPricesConstructor(): void
    {
        $data = [
            'extra_domain_price' => ['product' => ['currency' => 'EUR', 'price' => 20.0]],
            'extra_wildcard_domain_price' => ['product' => ['currency' => 'EUR', 'price' => 70.0]],
            'period' => 1,
            'price' => [
                'product' => ['currency' => 'EUR', 'price' => 99.99],
                'reseller' => ['currency' => 'USD', 'price' => 89.99],
            ],
        ];

        $prices = SslProductPrices::fromArray($data);

        $this->assertSame(1, $prices->period);
        $this->assertInstanceOf(SslProductPriceGroup::class, $prices->price);
        $this->assertSame(99.99, $prices->price->product->price);
        $this->assertSame(89.99, $prices->price->reseller->price);
        $this->assertSame(20.0, $prices->extra_domain_price->product->price);
        $this->assertSame(70.0, $prices->extra_wildcard_domain_price->product->price);
        $this->assertSame($data, $prices->toArray());
    }

    public function testSslProductPriceGroupConstructor(): void
    {
        $priceGroup = new SslProductPriceGroup(
            product: new SslProductPrice(currency: 'EUR', price: 49.0),
            reseller: new SslProductReseller(currency: 'EUR', price: 39.0),
        );

        $this->assertSame('EUR', $priceGroup->product->currency);
        $this->assertSame(49.0, $priceGroup->product->price);
        $this->assertInstanceOf(SslProductReseller::class, $priceGroup->reseller);
        $this->assertSame(39.0, $priceGroup->reseller->price);
        $this->assertSame([
            'product' => ['currency' => 'EUR', 'price' => 49.0],
            'reseller' => ['currency' => 'EUR', 'price' => 39.0],
        ], $priceGroup->toArray());
    }

    public function testSslProductLevelPricesConstructor(): void
    {
        $data = [
            'level' => 1,
            'name' => 'Tier 1',
            'prices' => [
                ['period' => 1, 'price' => ['product' => ['currency' => 'EUR', 'price' => 79.0]]],
                ['period' => 2, 'price' => ['product' => ['currency' => 'EUR', 'price' => 150.0]]],
            ],
        ];

        $levelPrices = SslProductLevelPrices::fromArray($data);

        $this->assertSame(1, $levelPrices->level);
        $this->assertSame('Tier 1', $levelPrices->name);
        $this->assertCount(2, $levelPrices->prices);
        $this->assertInstanceOf(SslProductPrices::class, $levelPrices->prices[1]);
        $this->assertSame(2, $levelPrices->prices[1]->period);
        $this->assertSame(150.0, $levelPrices->prices[1]->price->product->price);
        $this->assertSame($data, $levelPrices->toArray());
    }

    public function testSslProductSupportedSoftwareConstructor(): void
    {
        $software = new SslProductSupportedSoftware(id: 'linux', title: 'Apache + OpenSSL');

        $this->assertSame('linux', $software->id);
        $this->assertSame('Apache + OpenSSL', $software->title);
        $this->assertSame(['id' => 'linux', 'title' => 'Apache + OpenSSL'], $software->toArray());
        $this->assertEquals($software, SslProductSupportedSoftware::fromArray($software->toArray()));
    }

    public function testEmptySslProduct(): void
    {
        $product = new SslProduct();

        $this->assertNull($product->id);
        $this->assertNull($product->name);
        $this->assertNull((new SslProduct())->fromArray([])->prices);
    }

    public function testEmptySslProductPrice(): void
    {
        $price = new SslProductPrice();

        $this->assertNull($price->price);
        $this->assertNull($price->currency);
        $this->assertSame([], $price->toArray());
    }
}
