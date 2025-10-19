<?php

namespace Ashio\Tests\Model;

use Ashio\Model\Tld;
use Ashio\Model\TldApplicationMode;
use Ashio\Model\TldDescription;
use Ashio\Model\TldIdnScript;
use Ashio\Model\TldLevelPrice;
use Ashio\Model\TldPrices;
use PHPUnit\Framework\TestCase;

class TldCompleteModelsTest extends TestCase
{
    public function testTldConstructor(): void
    {
        $tld = new Tld();
        $tld->name = 'com';
        $tld->status = 'active';

        $this->assertEquals('com', $tld->name);
        $this->assertEquals('active', $tld->status);
    }

    public function testTldApplicationModeConstructor(): void
    {
        $mode = new TldApplicationMode();
        $mode->mode = 'standard';

        $this->assertEquals('standard', $mode->mode);
    }

    public function testTldDescriptionConstructor(): void
    {
        $description = new TldDescription();
        $description->language = 'en';
        $description->text = 'Commercial domains';

        $this->assertEquals('en', $description->language);
        $this->assertEquals('Commercial domains', $description->text);
    }

    public function testTldIdnScriptConstructor(): void
    {
        $script = new TldIdnScript();
        $script->name = 'Latin';

        $this->assertEquals('Latin', $script->name);
    }

    public function testTldLevelPriceFromArray(): void
    {
        $data = [
            'level' => 1,
            'price' => ['product' => ['name' => 'domain']],
        ];

        $levelPrice = (new TldLevelPrice())->fromArray($data);

        $this->assertEquals(1, $levelPrice->level);
        $this->assertInstanceOf(\Ashio\Model\TldPrice::class, $levelPrice->price);
    }

    public function testTldPricesConstructor(): void
    {
        $prices = new TldPrices();
        $prices->create = 10.00;
        $prices->renew = 12.00;

        $this->assertEquals(10.00, $prices->create);
        $this->assertEquals(12.00, $prices->renew);
    }

    public function testEmptyTld(): void
    {
        $tld = new Tld();

        $this->assertNull($tld->name);
        $this->assertNull($tld->status);
    }
}
