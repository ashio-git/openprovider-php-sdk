<?php

namespace Ashio\Tests\Model;

use Ashio\Model\AdditionalDataSelectOption;
use Ashio\Model\AdditionalDataSpecification;
use Ashio\Model\ContactAdditionalData;
use Ashio\Model\CustomerAdditionalData;
use Ashio\Model\CustomerExtensionAdditionalData;
use Ashio\Model\CustomerExtensionData;
use PHPUnit\Framework\TestCase;

class AdditionalDataModelsTest extends TestCase
{
    public function testAdditionalDataSelectOptionFromArray(): void
    {
        $data = [
            'value' => 'option1',
            'description' => 'Option 1',
        ];

        $option = (new AdditionalDataSelectOption())->fromArray($data);

        $this->assertEquals('option1', $option->value);
        $this->assertEquals('Option 1', $option->description);
    }

    public function testAdditionalDataSpecificationFromArray(): void
    {
        $data = [
            'name' => 'field1',
            'type' => 'text',
            'required' => true,
        ];

        $spec = (new AdditionalDataSpecification())->fromArray($data);

        $this->assertEquals('field1', $spec->name);
        $this->assertEquals('text', $spec->type);
    }

    public function testContactAdditionalDataFromArray(): void
    {
        $data = [
            'birth_date' => '1990-01-01',
            'birth_city' => 'Amsterdam',
            'social_security_number' => '123456789',
        ];

        $contactData = (new ContactAdditionalData())->fromArray($data);

        $this->assertEquals('1990-01-01', $contactData->birthDate);
        $this->assertEquals('Amsterdam', $contactData->birthCity);
        $this->assertEquals('123456789', $contactData->socialSecurityNumber);
    }

    public function testContactAdditionalDataToArray(): void
    {
        $contactData = new ContactAdditionalData();
        $contactData->birthDate = '1985-05-15';
        $contactData->birthCity = 'London';

        $array = $contactData->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('1985-05-15', $array['birth_date']);
        $this->assertEquals('London', $array['birth_city']);
    }

    public function testCustomerAdditionalDataFromArray(): void
    {
        $data = [
            'birth_city' => 'New York',
            'birth_date' => '1980-12-25',
            'passport_number' => 'AB123456',
        ];

        $customerData = (new CustomerAdditionalData())->fromArray($data);

        $this->assertEquals('New York', $customerData->birthCity);
        $this->assertEquals('1980-12-25', $customerData->birthDate);
        $this->assertEquals('AB123456', $customerData->passportNumber);
    }

    public function testCustomerExtensionDataFromArray(): void
    {
        $data = [
            'admin_type' => 'individual',
            'birth_date' => '1990-01-01',
            'passport_number' => 'XYZ789',
        ];

        $extData = (new CustomerExtensionData())->fromArray($data);

        $this->assertEquals('individual', $extData->adminType);
        $this->assertEquals('1990-01-01', $extData->birthDate);
        $this->assertEquals('XYZ789', $extData->passportNumber);
    }

    public function testEmptyAdditionalDataSelectOption(): void
    {
        $option = new AdditionalDataSelectOption();

        $this->assertNull($option->value);
        $this->assertNull($option->description);
    }

    public function testEmptyContactAdditionalData(): void
    {
        $data = new ContactAdditionalData();

        $this->assertNull($data->name);
        $this->assertNull($data->value);
    }
}
