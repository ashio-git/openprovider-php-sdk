<?php

namespace Ashio\Tests\Model;

use Ashio\Model\EmailField;
use Ashio\Model\EmailTag;
use Ashio\Model\EmailTemplate;
use Ashio\Model\Invoice;
use Ashio\Model\License;
use Ashio\Model\LicenseKey;
use Ashio\Model\OperationPrice;
use Ashio\Model\PremiumPrice;
use Ashio\Model\PriceGroup;
use Ashio\Model\ProductPrice;
use PHPUnit\Framework\TestCase;

class RemainingModelsTest extends TestCase
{
    public function testEmailFieldConstructor(): void
    {
        $field = (new EmailField())->fromArray(['name' => 'senderEmail', 'value' => 'support@reseller.example']);

        $this->assertSame('senderEmail', $field->name);
        $this->assertSame('support@reseller.example', $field->value);
    }

    public function testEmailTagConstructor(): void
    {
        $tag = (new EmailTag())->fromArray(['key' => 'customer', 'value' => 'VIP customer']);

        $this->assertSame('customer', $tag->key);
        $this->assertSame('VIP customer', $tag->value);
    }

    public function testEmailTemplateConstructor(): void
    {
        $template = (new EmailTemplate())->fromArray([
            'id' => 123,
            'name' => 'Welcome Template',
            'group' => 'tdv',
            'is_active' => true,
            'is_default' => false,
            'locale' => ['en_GB'],
            'fields' => [['name' => 'senderEmail', 'value' => 'support@reseller.example']],
            'tags' => [['key' => 'customer', 'value' => 'VIP customer']],
        ]);

        $this->assertSame(123, $template->id);
        $this->assertSame('Welcome Template', $template->name);
        $this->assertSame('tdv', $template->group);
        $this->assertTrue($template->is_active);
        $this->assertFalse($template->is_default);
        $this->assertSame(['en_GB'], $template->locale);
        $this->assertCount(1, $template->fields);
        $this->assertInstanceOf(EmailField::class, $template->fields[0]);
        $this->assertSame('senderEmail', $template->fields[0]->name);
        $this->assertCount(1, $template->tags);
        $this->assertInstanceOf(EmailTag::class, $template->tags[0]);
        $this->assertSame('VIP customer', $template->tags[0]->value);
    }

    public function testInvoiceConstructor(): void
    {
        $amount = ['product' => ['currency' => 'EUR', 'price' => 120.5]];
        $data = [
            'id' => 12345,
            'invoice_number' => 'INV-2024-001',
            'creation_date' => '2024-04-01 00:00:00',
            'amount' => $amount,
            'invoice_url' => 'https://rcp.openprovider.eu/invoice/12345',
            'attachment_url' => 'https://rcp.openprovider.eu/invoice/12345.pdf',
        ];

        $invoice = new Invoice($data);

        $this->assertSame(12345, $invoice->id);
        $this->assertSame('INV-2024-001', $invoice->invoice_number);
        $this->assertSame('2024-04-01 00:00:00', $invoice->creation_date);
        $this->assertSame($amount, $invoice->amount);
        $this->assertSame('https://rcp.openprovider.eu/invoice/12345', $invoice->invoice_url);
        $this->assertSame('https://rcp.openprovider.eu/invoice/12345.pdf', $invoice->attachment_url);
    }

    public function testLicenseKeyConstructor(): void
    {
        $licenseKey = new LicenseKey(key: 'ABCD-1234');

        $this->assertSame('ABCD-1234', $licenseKey->key);
        $this->assertSame(['key' => 'ABCD-1234'], $licenseKey->toArray());
        $this->assertEquals($licenseKey, LicenseKey::fromArray(['key' => 'ABCD-1234']));
        $this->assertSame([], (new LicenseKey())->toArray());
    }

    public function testLicenseConstructor(): void
    {
        $data = [
            'key_id' => 123,
            'key_number' => 'PLSK.01234567.0000',
            'product' => 'plesk',
            'status' => 'ACT',
            'title' => 'Plesk Web Admin Edition',
            'order_date' => '2024-01-01 00:00:00',
            'expiration_date' => '2025-01-01 00:00:00',
            'period' => 12,
            'ip_address_binding' => '192.0.2.10',
            'items' => ['PLESK-12-WEB-ADMIN'],
        ];

        $license = new License($data);

        $this->assertSame(123, $license->key_id);
        $this->assertSame('PLSK.01234567.0000', $license->key_number);
        $this->assertSame('plesk', $license->product);
        $this->assertSame('ACT', $license->status);
        $this->assertSame('Plesk Web Admin Edition', $license->title);
        $this->assertSame(12, $license->period);
        $this->assertSame('192.0.2.10', $license->ip_address_binding);
        $this->assertSame(['PLESK-12-WEB-ADMIN'], $license->items);
        $this->assertSame($data, $license->toArray());
    }

    public function testPremiumPriceConstructor(): void
    {
        $premium = new PremiumPrice(currency: 'USD', price: new OperationPrice(create: 1000.0));

        $this->assertSame('USD', $premium->currency);
        $this->assertInstanceOf(OperationPrice::class, $premium->price);
        $this->assertSame(1000.0, $premium->price->create);

        $data = ['currency' => 'USD', 'price' => ['create' => 1000.0]];
        $this->assertSame($data, $premium->toArray());
        $this->assertEquals($premium, PremiumPrice::fromArray($data));
    }

    public function testPriceGroupConstructor(): void
    {
        $product = (new ProductPrice())->fromArray(['currency' => 'EUR', 'price' => 10.0]);
        $reseller = (new ProductPrice())->fromArray(['currency' => 'USD', 'price' => 12.0]);

        $priceGroup = new PriceGroup(product: $product, reseller: $reseller);

        $this->assertSame($product, $priceGroup->product);
        $this->assertSame($reseller, $priceGroup->reseller);

        $data = [
            'product' => ['currency' => 'EUR', 'price' => 10.0],
            'reseller' => ['currency' => 'USD', 'price' => 12.0],
        ];
        $this->assertSame($data, $priceGroup->toArray());

        $parsed = PriceGroup::fromArray($data);
        $this->assertInstanceOf(ProductPrice::class, $parsed->product);
        $this->assertSame(10.0, $parsed->product->price);
        $this->assertSame('USD', $parsed->reseller->currency);
    }

    public function testOperationPriceConstructor(): void
    {
        $price = new OperationPrice(create: 10.99);

        $this->assertSame(10.99, $price->create);
        $this->assertSame(['create' => 10.99], $price->toArray());
        $this->assertEquals($price, OperationPrice::fromArray(['create' => 10.99]));
    }

    public function testEmptyEmailField(): void
    {
        $field = new EmailField();

        $this->assertNull($field->name);
        $this->assertNull($field->value);
    }

    public function testEmptyInvoice(): void
    {
        $invoice = new Invoice();

        $this->assertNull($invoice->id);
        $this->assertNull($invoice->invoice_number);
    }

    public function testEmptyLicense(): void
    {
        $license = new License();

        $this->assertNull($license->key_id);
        $this->assertNull($license->key_number);
        $this->assertSame([], $license->toArray());
    }
}
