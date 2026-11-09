<?php

namespace Ashio\Tests\Model;

use Ashio\Model\SettingsPaymentMethodLimit;
use Ashio\Model\SettingsPaymentMethodLimits;
use Ashio\Model\SettingsPaymentMethods;
use Ashio\Model\SettingsPaymentMethodSettings;
use Ashio\Model\SettingsSignedContracts;
use Ashio\Model\SettingsTacContract;
use PHPUnit\Framework\TestCase;

class SettingsCompleteModelsTest extends TestCase
{
    public function testSettingsPaymentMethodLimitConstructor(): void
    {
        $limit = (new SettingsPaymentMethodLimit())->fromArray([
            'EUR' => 10.0,
            'USD' => 12.5,
            'XXX' => 99.0, // not a supported currency: ignored
        ]);

        $this->assertSame(10.0, $limit->EUR);
        $this->assertSame(12.5, $limit->USD);
        $this->assertNull($limit->GBP);
        $this->assertSame(['EUR' => 10.0, 'USD' => 12.5], $limit->toArray());
    }

    public function testSettingsPaymentMethodLimitsConstructor(): void
    {
        $data = [
            'maximum' => ['EUR' => 5000.0, 'USD' => 5500.0],
            'minimum' => ['EUR' => 10.0],
        ];

        $limits = (new SettingsPaymentMethodLimits())->fromArray($data);

        $this->assertInstanceOf(SettingsPaymentMethodLimit::class, $limits->maximum);
        $this->assertSame(5000.0, $limits->maximum->EUR);
        $this->assertSame(5500.0, $limits->maximum->USD);
        $this->assertInstanceOf(SettingsPaymentMethodLimit::class, $limits->minimum);
        $this->assertSame(10.0, $limits->minimum->EUR);
        $this->assertSame($data, $limits->toArray());
    }

    public function testSettingsPaymentMethodsConstructor(): void
    {
        $data = [
            'name' => 'creditcard',
            'settings' => [
                'transaction_fee' => 2.5,
                'transaction_fee_absolute' => 0.3,
                'limit' => ['maximum' => ['EUR' => 1000.0], 'minimum' => ['EUR' => 10.0]],
            ],
        ];

        $methods = (new SettingsPaymentMethods())->fromArray($data);

        $this->assertSame('creditcard', $methods->name);
        $this->assertInstanceOf(SettingsPaymentMethodSettings::class, $methods->settings);
        $this->assertSame(2.5, $methods->settings->transactionFee);
        $this->assertSame(1000.0, $methods->settings->limit->maximum->EUR);
        $this->assertSame($data, $methods->toArray());
    }

    public function testSettingsPaymentMethodSettingsConstructor(): void
    {
        $settings = (new SettingsPaymentMethodSettings())->fromArray([
            'limit' => ['minimum' => ['EUR' => 25.0]],
            'transaction_fee' => 1.9,
            'transaction_fee_absolute' => 0.25,
        ]);

        $this->assertInstanceOf(SettingsPaymentMethodLimits::class, $settings->limit);
        $this->assertSame(25.0, $settings->limit->minimum->EUR);
        $this->assertNull($settings->limit->maximum);
        $this->assertSame(1.9, $settings->transactionFee);
        $this->assertSame(0.25, $settings->transactionFeeAbsolute);
        $this->assertSame([
            'transaction_fee' => 1.9,
            'transaction_fee_absolute' => 0.25,
            'limit' => ['minimum' => ['EUR' => 25.0]],
        ], $settings->toArray());
    }

    public function testSettingsSignedContractsConstructor(): void
    {
        $data = [
            'contents' => '<p>Reseller agreement</p>',
            'id' => 5,
            'is_signed' => false,
            'modification_date' => '2024-02-01 00:00:00',
            'sign_on_date' => '2024-03-01 00:00:00',
            'title' => 'Reseller agreement',
            'type' => 'reseller',
            'version_id' => 12,
        ];

        $contracts = (new SettingsSignedContracts())->fromArray($data);

        $this->assertSame('<p>Reseller agreement</p>', $contracts->contents);
        $this->assertSame(5, $contracts->id);
        $this->assertFalse($contracts->isSigned);
        $this->assertSame('2024-02-01 00:00:00', $contracts->modificationDate);
        $this->assertSame('2024-03-01 00:00:00', $contracts->signOnDate);
        $this->assertSame('Reseller agreement', $contracts->title);
        $this->assertSame('reseller', $contracts->type);
        $this->assertSame(12, $contracts->versionId);
        $this->assertSame($data, $contracts->toArray());
    }

    public function testSettingsTacContractConstructor(): void
    {
        $data = [
            'is_signed' => true,
            'name' => 'General Terms and Conditions',
            'signed_at' => '2024-01-10 08:00:00',
            'type' => 'tac',
        ];

        $contract = (new SettingsTacContract())->fromArray($data);

        $this->assertTrue($contract->isSigned);
        $this->assertSame('General Terms and Conditions', $contract->name);
        $this->assertSame('2024-01-10 08:00:00', $contract->signedAt);
        $this->assertSame('tac', $contract->type);
        $this->assertSame($data, $contract->toArray());
    }

    public function testEmptySettingsPaymentMethodLimit(): void
    {
        $limit = new SettingsPaymentMethodLimit();

        $this->assertNull($limit->EUR);
        $this->assertNull($limit->USD);
        $this->assertSame([], $limit->toArray());
    }
}
