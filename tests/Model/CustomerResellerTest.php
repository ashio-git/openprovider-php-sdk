<?php

namespace Ashio\Tests\Model;

use Ashio\Model\Customer;
use Ashio\Model\Reseller;
use Ashio\Model\ResellerSettings;
use Ashio\Model\ResellerStatistics;
use Ashio\Model\Tag;
use Ashio\Model\DomainEmailVerification;
use PHPUnit\Framework\TestCase;

class CustomerResellerTest extends TestCase
{
    public function testCustomerFromArray()
    {
        $data = [
            'handle' => 'CUST123',
            'email' => 'customer@example.com',
            'company_name' => 'Customer Inc',
        ];
        
        $customer = (new Customer())->fromArray($data);
        
        $this->assertEquals('CUST123', $customer->handle);
        $this->assertEquals('customer@example.com', $customer->email);
        $this->assertEquals('Customer Inc', $customer->companyName);
    }

    public function testCustomerToArray()
    {
        $customer = new Customer();
        $customer->handle = 'CUST456';
        $customer->email = 'test@example.com';
        
        $array = $customer->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('handle', $array);
        $this->assertArrayHasKey('email', $array);
    }

    public function testResellerFromArray()
    {
        $data = [
            'id' => 789,
            'company_name' => 'Reseller Corp',
            'email' => 'reseller@example.com',
        ];
        
        $reseller = (new Reseller())->fromArray($data);
        
        $this->assertEquals(789, $reseller->id);
        $this->assertEquals('Reseller Corp', $reseller->companyName);
        $this->assertEquals('reseller@example.com', $reseller->email);
    }

    public function testResellerToArray()
    {
        $reseller = new Reseller();
        $reseller->id = 100;
        $reseller->companyName = 'Test Reseller';
        
        $array = $reseller->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('company_name', $array);
    }

    public function testResellerSettingsFromArray()
    {
        $data = [
            'default_nameservers' => ['ns1.example.com', 'ns2.example.com'],
            'default_dns_template_id' => 123,
            'auto_renew_domains' => true,
        ];
        
        $settings = (new ResellerSettings())->fromArray($data);
        
        $this->assertEquals(['ns1.example.com', 'ns2.example.com'], $settings->defaultNameservers);
        $this->assertEquals(123, $settings->defaultDnsTemplateId);
        $this->assertTrue($settings->autoRenewDomains);
    }

    public function testResellerSettingsConstructorWithData()
    {
        $data = ['auto_renew_ssl' => true];
        
        $settings = new ResellerSettings($data);
        
        $this->assertTrue($settings->autoRenewSsl);
    }

    public function testResellerStatisticsFromArray()
    {
        $data = [
            'total_domains' => 150,
            'active_domains' => 120,
            'total_ssl' => 25,
        ];
        
        $stats = (new ResellerStatistics())->fromArray($data);
        
        $this->assertEquals(150, $stats->totalDomains);
        $this->assertEquals(120, $stats->activeDomains);
        $this->assertEquals(25, $stats->totalSsl);
    }

    public function testResellerStatisticsConstructorWithData()
    {
        $data = ['total_licenses' => 200];
        
        $stats = new ResellerStatistics($data);
        
        $this->assertEquals(200, $stats->totalLicenses);
    }

    public function testTagFromArray()
    {
        $data = [
            'key' => 'environment',
            'value' => 'production',
        ];
        
        $tag = (new Tag())->fromArray($data);
        
        $this->assertEquals('environment', $tag->key);
        $this->assertEquals('production', $tag->value);
    }

    public function testTagToArray()
    {
        $tag = new Tag();
        $tag->key = 'type';
        $tag->value = 'test';
        
        $array = $tag->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('type', $array['key']);
        $this->assertEquals('test', $array['value']);
    }

    public function testDomainEmailVerificationFromArray()
    {
        $data = [
            'email' => 'verify@example.com',
            'status' => 'verified',
            'domain' => 'example.com',
        ];
        
        $verification = (new DomainEmailVerification())->fromArray($data);
        
        $this->assertEquals('verify@example.com', $verification->email);
        $this->assertEquals('verified', $verification->status);
    }

    public function testEmptyCustomer()
    {
        $customer = new Customer();
        
        $this->assertNull($customer->handle);
        $this->assertNull($customer->email);
    }

    public function testEmptyReseller()
    {
        $reseller = new Reseller();
        
        $this->assertNull($reseller->id);
        $this->assertNull($reseller->companyName);
    }

    public function testEmptyTag()
    {
        $tag = new Tag();
        
        $this->assertNull($tag->key);
        $this->assertNull($tag->value);
    }

    public function testCustomerWithAllFields()
    {
        $data = [
            'handle' => 'FULL123',
            'email' => 'full@example.com',
            'company_name' => 'Full Company',
            'phone' => ['country_code' => '+1', 'subscriber_number' => '5551234'],
            'address' => ['street' => 'Main St', 'city' => 'City', 'country' => 'US'],
        ];
        
        $customer = (new Customer())->fromArray($data);
        
        $this->assertEquals('FULL123', $customer->handle);
        $this->assertEquals('full@example.com', $customer->email);
        $this->assertEquals('Full Company', $customer->companyName);
    }
}
