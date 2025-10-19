<?php

namespace Ashio\Tests\Model;

use Ashio\Model\DomainName;
use Ashio\Model\EmailField;
use Ashio\Model\EmailTag;
use Ashio\Model\EmailTemplate;
use Ashio\Model\Invoice;
use Ashio\Model\LicenseKey;
use Ashio\Model\License;
use Ashio\Model\OperationPrice;
use Ashio\Model\PremiumPrice;
use Ashio\Model\PriceGroup;
use PHPUnit\Framework\TestCase;

class RemainingModelsTest extends TestCase
{
    public function testEmailFieldConstructor(): void
    {
        $field = new EmailField();
        $field->name = 'subject';
        $field->value = 'Welcome';

        $this->assertEquals('subject', $field->name);
        $this->assertEquals('Welcome', $field->value);
    }

    public function testEmailTagConstructor(): void
    {
        $tag = new EmailTag();
        $tag->key = 'customer_name';
        $tag->value = 'John Doe';

        $this->assertEquals('customer_name', $tag->key);
        $this->assertEquals('John Doe', $tag->value);
    }

    public function testEmailTemplateConstructor(): void
    {
        $template = new EmailTemplate();
        $template->id = 123;
        $template->name = 'Welcome Template';

        $this->assertEquals(123, $template->id);
        $this->assertEquals('Welcome Template', $template->name);
    }

    public function testInvoiceConstructor(): void
    {
        $data = [
            'id' => 12345,
            'invoice_number' => 'INV-2024-001',
        ];

        $invoice = new Invoice($data);

        $this->assertEquals(12345, $invoice->id);
        $this->assertEquals('INV-2024-001', $invoice->invoice_number);
    }

    public function testLicenseKeyConstructor(): void
    {
        $licenseKey = new LicenseKey();
        $licenseKey->key = 'ABCD-1234';
        $licenseKey->product = 'Plesk';

        $this->assertEquals('ABCD-1234', $licenseKey->key);
        $this->assertEquals('Plesk', $licenseKey->product);
    }

    public function testLicenseConstructor(): void
    {
        $license = new License();
        $license->id = 999;
        $license->key_id = 123;

        $this->assertEquals(999, $license->id);
        $this->assertEquals(123, $license->key_id);
    }

    public function testPremiumPriceConstructor(): void
    {
        $premium = new PremiumPrice();
        $premium->price = 1000.00;
        $premium->currency = 'USD';

        $this->assertEquals(1000.00, $premium->price);
        $this->assertEquals('USD', $premium->currency);
    }

    public function testPriceGroupConstructor(): void
    {
        $priceGroup = new PriceGroup();
        $priceGroup->create = 10.00;
        $priceGroup->renew = 12.00;

        $this->assertEquals(10.00, $priceGroup->create);
        $this->assertEquals(12.00, $priceGroup->renew);
    }

    public function testOperationPriceConstructor(): void
    {
        $price = new OperationPrice();
        $price->operation = 'create';
        $price->price = 10.99;

        $this->assertEquals('create', $price->operation);
        $this->assertEquals(10.99, $price->price);
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

        $this->assertNull($license->id);
        $this->assertNull($license->key_id);
    }
}
