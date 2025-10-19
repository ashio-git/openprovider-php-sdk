<?php

namespace Ashio\Tests\Model;

use Ashio\Model\Payment;
use Ashio\Model\Transaction;
use Ashio\Model\Price;
use Ashio\Model\ProductPrice;
use Ashio\Model\PromotionData;
use PHPUnit\Framework\TestCase;

class PaymentTransactionModelsTest extends TestCase
{
    public function testPaymentConstructor(): void
    {
        $data = [
            'id' => 123,
            'status' => 'completed',
            'amount' => ['value' => 99.99, 'currency' => 'USD'],
        ];

        $payment = new Payment($data);

        $this->assertEquals(123, $payment->id);
        $this->assertEquals('completed', $payment->status);
        $this->assertIsArray($payment->amount);
    }

    public function testTransactionConstructor(): void
    {
        $transaction = new Transaction();
        $transaction->id = 456;
        $transaction->amount = 50.00;
        $transaction->status = 'completed';

        $this->assertEquals(456, $transaction->id);
        $this->assertEquals(50.00, $transaction->amount);
        $this->assertEquals('completed', $transaction->status);
    }

    public function testPriceConstructor(): void
    {
        $price = new Price();
        $price->amount = 10.00;
        $price->currency = 'EUR';

        $this->assertEquals(10.00, $price->amount);
        $this->assertEquals('EUR', $price->currency);
    }

    public function testProductPriceConstructor(): void
    {
        $productPrice = new ProductPrice();
        $productPrice->product = 'domain';
        $productPrice->price = 15.00;

        $this->assertEquals('domain', $productPrice->product);
        $this->assertEquals(15.00, $productPrice->price);
    }

    public function testPromotionDataConstructor(): void
    {
        $promotion = new PromotionData();
        $promotion->code = 'SAVE20';
        $promotion->discount = 20.00;

        $this->assertEquals('SAVE20', $promotion->code);
        $this->assertEquals(20.00, $promotion->discount);
    }

    public function testEmptyPayment(): void
    {
        $payment = new Payment();

        $this->assertNull($payment->id);
        $this->assertNull($payment->amount);
    }

    public function testEmptyTransaction(): void
    {
        $transaction = new Transaction();

        $this->assertNull($transaction->id);
        $this->assertNull($transaction->status);
    }
}
