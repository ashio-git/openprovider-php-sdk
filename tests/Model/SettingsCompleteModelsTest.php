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
        $limit = new SettingsPaymentMethodLimit();
        $limit->min = 10.00;
        $limit->max = 1000.00;

        $this->assertEquals(10.00, $limit->min);
        $this->assertEquals(1000.00, $limit->max);
    }

    public function testSettingsPaymentMethodLimitsConstructor(): void
    {
        $limits = new SettingsPaymentMethodLimits();
        $limits->daily = 500.00;
        $limits->monthly = 5000.00;

        $this->assertEquals(500.00, $limits->daily);
        $this->assertEquals(5000.00, $limits->monthly);
    }

    public function testSettingsPaymentMethodsConstructor(): void
    {
        $methods = new SettingsPaymentMethods();
        $methods->creditCard = true;
        $methods->bankTransfer = false;

        $this->assertTrue($methods->creditCard);
        $this->assertFalse($methods->bankTransfer);
    }

    public function testSettingsPaymentMethodSettingsConstructor(): void
    {
        $settings = new SettingsPaymentMethodSettings();
        $settings->enabled = true;

        $this->assertTrue($settings->enabled);
    }

    public function testSettingsSignedContractsConstructor(): void
    {
        $contracts = new SettingsSignedContracts();
        $contracts->count = 5;

        $this->assertEquals(5, $contracts->count);
    }

    public function testSettingsTacContractConstructor(): void
    {
        $contract = new SettingsTacContract();
        $contract->version = '1.0';
        $contract->signed = true;

        $this->assertEquals('1.0', $contract->version);
        $this->assertTrue($contract->signed);
    }

    public function testEmptySettingsPaymentMethodLimit(): void
    {
        $limit = new SettingsPaymentMethodLimit();

        $this->assertNull($limit->min);
        $this->assertNull($limit->max);
    }
}
