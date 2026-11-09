<?php

namespace Ashio\Tests\Model;

use Ashio\Model\Payment;
use Ashio\Model\Price;
use Ashio\Model\PriceGroup;
use Ashio\Model\ProductPrice;
use Ashio\Model\PromotionData;
use Ashio\Model\Transaction;
use PHPUnit\Framework\TestCase;

class PaymentTransactionModelsTest extends TestCase
{
    public function testPaymentConstructor(): void
    {
        $amount = [
            'product' => ['currency' => 'USD', 'price' => 99.99],
            'reseller' => ['currency' => 'EUR', 'price' => 91.5],
        ];
        $data = [
            'id' => 123,
            'status' => 'completed',
            'creation_date' => '2024-03-01 09:00:00',
            'payment_date' => '2024-03-01 09:05:00',
            'confirmation_date' => '2024-03-02 12:00:00',
            'method' => 'creditcard',
            'type' => 'deposit',
            'amount' => $amount,
        ];

        $payment = new Payment($data);

        $this->assertSame(123, $payment->id);
        $this->assertSame('completed', $payment->status);
        $this->assertSame('2024-03-01 09:00:00', $payment->creation_date);
        $this->assertSame('2024-03-01 09:05:00', $payment->payment_date);
        $this->assertSame('2024-03-02 12:00:00', $payment->confirmation_date);
        $this->assertSame('creditcard', $payment->method);
        $this->assertSame('deposit', $payment->type);
        // amount is kept as the raw API structure.
        $this->assertSame($amount, $payment->amount);
    }

    public function testTransactionConstructor(): void
    {
        $total = [
            'product' => ['currency' => 'EUR', 'price' => 50.0],
            'reseller' => ['currency' => 'EUR', 'price' => 50.0],
        ];

        $transaction = new Transaction([
            'reference_id' => 456,
            'action' => 'create',
            'creation_date' => '2024-02-10 14:30:00',
            'subject' => 'example.com',
            'total' => $total,
        ]);

        $this->assertSame(456, $transaction->reference_id);
        $this->assertSame('create', $transaction->action);
        $this->assertSame('2024-02-10 14:30:00', $transaction->creation_date);
        $this->assertSame('example.com', $transaction->subject);
        $this->assertSame($total, $transaction->total);
    }

    public function testPriceConstructor(): void
    {
        $price = (new Price())->fromArray([
            'is_premium' => false,
            'is_promotion' => true,
            'membership_price' => ['product' => ['currency' => 'EUR', 'price' => 8.5]],
            'price' => [
                'product' => ['currency' => 'EUR', 'price' => 10.0],
                'reseller' => ['currency' => 'USD', 'price' => 11.2],
            ],
            'promotion_data' => ['start_date' => '2024-01-01', 'end_date' => '2024-01-31'],
        ]);

        $this->assertFalse($price->is_premium);
        $this->assertTrue($price->is_promotion);

        $this->assertInstanceOf(PriceGroup::class, $price->price);
        $this->assertInstanceOf(ProductPrice::class, $price->price->product);
        $this->assertSame('EUR', $price->price->product->currency);
        $this->assertSame(10.0, $price->price->product->price);
        $this->assertSame('USD', $price->price->reseller->currency);
        $this->assertSame(11.2, $price->price->reseller->price);

        $this->assertInstanceOf(PriceGroup::class, $price->membership_price);
        $this->assertSame(8.5, $price->membership_price->product->price);
        $this->assertNull($price->membership_price->reseller);

        $this->assertInstanceOf(PromotionData::class, $price->promotion_data);
        $this->assertSame('2024-01-01', $price->promotion_data->start_date);
        $this->assertSame('2024-01-31', $price->promotion_data->end_date);

        $this->assertNull($price->tier_price);
    }

    public function testProductPriceConstructor(): void
    {
        $data = ['currency' => 'EUR', 'price' => 15.0];

        $productPrice = (new ProductPrice())->fromArray($data);

        $this->assertSame('EUR', $productPrice->currency);
        $this->assertSame(15.0, $productPrice->price);
        $this->assertSame($data, $productPrice->toArray());
        $this->assertSame(['price' => 0.0], (new ProductPrice())->fromArray(['price' => 0.0])->toArray());
    }

    public function testPromotionDataConstructor(): void
    {
        $promotion = new PromotionData(end_date: '2024-12-31', start_date: '2024-12-01');

        $this->assertSame('2024-12-31', $promotion->end_date);
        $this->assertSame('2024-12-01', $promotion->start_date);
        $this->assertSame(['end_date' => '2024-12-31', 'start_date' => '2024-12-01'], $promotion->toArray());
        $this->assertEquals($promotion, PromotionData::fromArray($promotion->toArray()));
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

        $this->assertNull($transaction->reference_id);
        $this->assertNull($transaction->action);
        $this->assertNull($transaction->total);
    }
}
