<?php

namespace Ashio\Tests\Model;

use Ashio\Model\SettingsTransactionFee;
use Ashio\Model\Tag;
use PHPUnit\Framework\TestCase;

class SettingsTransactionModelsTest extends TestCase
{
    public function testSettingsTransactionFeeConstructor(): void
    {
        $fee = new SettingsTransactionFee();
        $fee->percentage = 2.5;
        $fee->fixed = 0.50;

        $this->assertEquals(2.5, $fee->percentage);
        $this->assertEquals(0.50, $fee->fixed);
    }

    public function testTagConstructor(): void
    {
        $tag = new Tag();
        $tag->key = 'environment';
        $tag->value = 'production';

        $this->assertEquals('environment', $tag->key);
        $this->assertEquals('production', $tag->value);
    }

    public function testEmptySettingsTransactionFee(): void
    {
        $fee = new SettingsTransactionFee();

        $this->assertNull($fee->percentage);
        $this->assertNull($fee->fixed);
    }

    public function testEmptyTag(): void
    {
        $tag = new Tag();

        $this->assertNull($tag->key);
        $this->assertNull($tag->value);
    }
}
