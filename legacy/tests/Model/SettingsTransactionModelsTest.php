<?php

namespace Ashio\Tests\Model;

use Ashio\Model\SettingsTransactionFee;
use Ashio\Model\Tag;
use PHPUnit\Framework\TestCase;

class SettingsTransactionModelsTest extends TestCase
{
    public function testSettingsTransactionFeeConstructor(): void
    {
        $data = [
            'bank' => 0.5,
            'credit_card' => 2.5,
            'ideal' => 0.3,
            'paypal' => 3.4,
            'western_union' => 5.0,
            'yandex' => 2.8,
        ];

        $fee = (new SettingsTransactionFee())->fromArray($data);

        $this->assertSame(0.5, $fee->bank);
        $this->assertSame(2.5, $fee->creditCard);
        $this->assertSame(0.3, $fee->ideal);
        $this->assertSame(3.4, $fee->paypal);
        $this->assertSame(5.0, $fee->westernUnion);
        $this->assertSame(2.8, $fee->yandex);
        $this->assertSame($data, $fee->toArray());
    }

    public function testTagConstructor(): void
    {
        $tag = (new Tag())->fromArray(['key' => 'customer', 'value' => 'VIP customer']);

        $this->assertSame('customer', $tag->key);
        $this->assertSame('VIP customer', $tag->value);
        $this->assertSame(['key' => 'customer', 'value' => 'VIP customer'], $tag->toArray());
    }

    public function testEmptySettingsTransactionFee(): void
    {
        $fee = new SettingsTransactionFee();

        $this->assertNull($fee->bank);
        $this->assertNull($fee->creditCard);
        $this->assertSame([], $fee->toArray());
    }

    public function testEmptyTag(): void
    {
        $tag = new Tag();

        $this->assertNull($tag->key);
        $this->assertNull($tag->value);
        $this->assertSame([], $tag->toArray());
    }
}
