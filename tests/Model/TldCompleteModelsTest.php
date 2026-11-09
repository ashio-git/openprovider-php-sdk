<?php

namespace Ashio\Tests\Model;

use Ashio\Model\Tld;
use Ashio\Model\TldApplicationMode;
use Ashio\Model\TldDescription;
use Ashio\Model\TldIdnScript;
use Ashio\Model\TldLevelPrice;
use Ashio\Model\TldPrice;
use Ashio\Model\TldPrices;
use PHPUnit\Framework\TestCase;

class TldCompleteModelsTest extends TestCase
{
    public function testTldConstructor(): void
    {
        $tld = new Tld([
            'name' => 'com',
            'status' => 'ACT',
            'min_period' => 1,
            'max_period' => 10,
            'is_private_whois_allowed' => true,
            'dnssec_allowed' => true,
            'dnssec_algorithms' => [8, 13],
            'restrictions' => ['none'],
            'usage_count' => 42,
            'description' => ['text' => 'Commercial domains', 'url' => 'https://www.verisign.com'],
            'prices' => [
                'min_period' => 1,
                'max_period' => 10,
                'create_price' => ['product' => ['currency' => 'USD', 'price' => 10.5]],
            ],
            'supported_application_mode' => [['name' => 'standard', 'title' => 'Standard']],
            'supported_idn_scripts' => [['description' => 'Latin', 'value' => 'LATN']],
            'level_prices' => [['level' => 1, 'price' => ['product' => ['currency' => 'USD', 'price' => 9.0]]]],
            'quarantine_period' => 30,
            'trade_available' => false,
            'transfer_available' => true,
            'unknown_flag' => true,
        ]);

        $this->assertSame('com', $tld->name);
        $this->assertSame('ACT', $tld->status);
        $this->assertSame(1, $tld->min_period);
        $this->assertSame(10, $tld->max_period);
        $this->assertTrue($tld->is_private_whois_allowed);
        $this->assertTrue($tld->dnssec_allowed);
        $this->assertSame([8, 13], $tld->dnssec_algorithms);
        $this->assertSame(['none'], $tld->restrictions);
        $this->assertSame(42, $tld->usage_count);

        $this->assertInstanceOf(TldDescription::class, $tld->description);
        $this->assertSame('Commercial domains', $tld->description->text);

        $this->assertInstanceOf(TldPrices::class, $tld->prices);
        $this->assertInstanceOf(TldPrice::class, $tld->prices->create_price);
        $this->assertSame(['currency' => 'USD', 'price' => 10.5], $tld->prices->create_price->product);

        $this->assertInstanceOf(TldApplicationMode::class, $tld->supported_application_mode[0]);
        $this->assertSame('standard', $tld->supported_application_mode[0]->name);
        $this->assertInstanceOf(TldIdnScript::class, $tld->supported_idn_scripts[0]);
        $this->assertSame('LATN', $tld->supported_idn_scripts[0]->value);
        $this->assertInstanceOf(TldLevelPrice::class, $tld->levelPrices[0]);
        $this->assertSame(1, $tld->levelPrices[0]->level);

        // snake_case API keys mapped onto camelCase properties
        $this->assertSame(30, $tld->quarantinePeriod);
        $this->assertFalse($tld->tradeAvailable);
        $this->assertTrue($tld->transferAvailable);

        $this->assertFalse(property_exists($tld, 'unknown_flag'));
    }

    public function testTldApplicationModeConstructor(): void
    {
        $mode = (new TldApplicationMode())->fromArray(['name' => 'sunrise', 'title' => 'Sunrise period']);

        $this->assertSame('sunrise', $mode->name);
        $this->assertSame('Sunrise period', $mode->title);
    }

    public function testTldDescriptionConstructor(): void
    {
        $description = new TldDescription([
            'text' => 'Commercial domains',
            'url' => 'https://www.verisign.com',
            'language' => 'en', // not part of the model: ignored
        ]);

        $this->assertSame('Commercial domains', $description->text);
        $this->assertSame('https://www.verisign.com', $description->url);
        $this->assertFalse(property_exists($description, 'language'));
    }

    public function testTldIdnScriptConstructor(): void
    {
        $script = (new TldIdnScript())->fromArray(['description' => 'Latin', 'value' => 'LATN']);

        $this->assertSame('Latin', $script->description);
        $this->assertSame('LATN', $script->value);
    }

    public function testTldLevelPriceFromArray(): void
    {
        $data = [
            'level' => 1,
            'price' => [
                'product' => ['currency' => 'EUR', 'price' => 8.99],
                'reseller' => ['currency' => 'USD', 'price' => 9.75],
            ],
        ];

        $levelPrice = (new TldLevelPrice())->fromArray($data);

        $this->assertSame(1, $levelPrice->level);
        $this->assertInstanceOf(TldPrice::class, $levelPrice->price);
        $this->assertSame(['currency' => 'EUR', 'price' => 8.99], $levelPrice->price->product);
        $this->assertSame(['currency' => 'USD', 'price' => 9.75], $levelPrice->price->reseller);
        $this->assertNull((new TldLevelPrice())->fromArray(['level' => 2])->price);
    }

    public function testTldPricesConstructor(): void
    {
        $prices = new TldPrices([
            'min_period' => 1,
            'max_period' => 10,
            'create_price' => ['product' => ['currency' => 'EUR', 'price' => 10.0]],
            'renew_price' => ['product' => ['currency' => 'EUR', 'price' => 12.0]],
            'transfer_price' => ['reseller' => ['currency' => 'USD', 'price' => 11.0]],
        ]);

        $this->assertSame(1, $prices->min_period);
        $this->assertSame(10, $prices->max_period);
        $this->assertInstanceOf(TldPrice::class, $prices->create_price);
        $this->assertSame(['currency' => 'EUR', 'price' => 10.0], $prices->create_price->product);
        $this->assertInstanceOf(TldPrice::class, $prices->renew_price);
        $this->assertSame(12.0, $prices->renew_price->product['price']);
        $this->assertSame(['currency' => 'USD', 'price' => 11.0], $prices->transfer_price->reseller);
        $this->assertNull($prices->transfer_price->product);
        $this->assertNull($prices->restore_price);
    }

    public function testEmptyTld(): void
    {
        $tld = new Tld();

        $this->assertNull($tld->name);
        $this->assertNull($tld->status);
    }
}
