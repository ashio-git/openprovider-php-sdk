<?php

namespace Ashio\Tests\Model;

use Ashio\Model\AdditionalDataSelectOption;
use Ashio\Model\AdditionalDataSpecification;
use Ashio\Model\ContactAdditionalData;
use Ashio\Model\CustomerAdditionalData;
use Ashio\Model\CustomerExtensionData;
use Ashio\Model\File;
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
            'attached_files' => [
                ['description' => 'Passport scan', 'doc_type' => 'passport', 'path' => '/uploads/passport.pdf'],
            ],
            'birth_city' => 'New York',
            'birth_date' => '1980-12-25',
            'passport_number' => 'AB123456',
        ];

        $customerData = CustomerAdditionalData::fromArray($data);

        $this->assertSame('New York', $customerData->birth_city);
        $this->assertSame('1980-12-25', $customerData->birth_date);
        $this->assertSame('AB123456', $customerData->passport_number);
        $this->assertNull($customerData->trading_name);
        $this->assertCount(1, $customerData->attached_files);
        $this->assertInstanceOf(File::class, $customerData->attached_files[0]);
        $this->assertSame('passport', $customerData->attached_files[0]->doc_type);
        $this->assertSame('/uploads/passport.pdf', $customerData->attached_files[0]->path);

        // toArray() drops null fields and serialises nested files back to API arrays.
        $this->assertSame($data, $customerData->toArray());
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

        $this->assertNull($data->birthDate);
        $this->assertNull($data->birthCity);
        $this->assertNull($data->socialSecurityNumber);
        // ContactAdditionalData::toArray() keeps null keys (no filtering).
        $this->assertSame([
            'birth_date' => null,
            'birth_city' => null,
            'social_security_number' => null,
        ], $data->toArray());
    }
}
