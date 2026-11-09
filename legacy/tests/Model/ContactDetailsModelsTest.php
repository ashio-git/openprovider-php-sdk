<?php

namespace Ashio\Tests\Model;

use Ashio\Model\ContactAddress;
use Ashio\Model\ContactClientIpList;
use Ashio\Model\ContactName;
use Ashio\Model\ContactPhone;
use Ashio\Model\CustomerFax;
use Ashio\Model\CustomerFile;
use Ashio\Model\CustomerTag;
use PHPUnit\Framework\TestCase;

class ContactDetailsModelsTest extends TestCase
{
    public function testContactAddressFromArray(): void
    {
        $data = [
            'street' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'zipcode' => '10001',
            'country' => 'US',
        ];

        $address = (new ContactAddress())->fromArray($data);

        $this->assertEquals('123 Main St', $address->street);
        $this->assertEquals('New York', $address->city);
        $this->assertEquals('NY', $address->state);
        $this->assertEquals('10001', $address->zipcode);
        $this->assertEquals('US', $address->country);
    }

    public function testContactAddressToArray(): void
    {
        $address = new ContactAddress();
        $address->street = 'Test St';
        $address->city = 'Test City';
        $address->country = 'US';

        $array = $address->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Test St', $array['street']);
        $this->assertEquals('Test City', $array['city']);
    }

    public function testContactNameFromArray(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'initials' => 'J.D.',
        ];

        $name = (new ContactName())->fromArray($data);

        $this->assertEquals('John', $name->firstName);
        $this->assertEquals('Doe', $name->lastName);
    }

    public function testContactPhoneFromArray(): void
    {
        $data = [
            'country_code' => '+1',
            'area_code' => '555',
            'subscriber_number' => '1234567',
        ];

        $phone = (new ContactPhone())->fromArray($data);

        $this->assertEquals('+1', $phone->countryCode);
        $this->assertEquals('555', $phone->areaCode);
        $this->assertEquals('1234567', $phone->subscriberNumber);
    }

    public function testContactPhoneToArray(): void
    {
        $phone = new ContactPhone();
        $phone->countryCode = '+44';
        $phone->subscriberNumber = '7700900000';

        $array = $phone->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('+44', $array['country_code']);
    }

    public function testCustomerFaxFromArray(): void
    {
        $data = [
            'country_code' => '+1',
            'subscriber_number' => '5551234',
        ];

        $fax = (new CustomerFax())->fromArray($data);

        $this->assertEquals('+1', $fax->countryCode);
        $this->assertEquals('5551234', $fax->subscriberNumber);
    }

    public function testCustomerFileFromArray(): void
    {
        $data = [
            'description' => 'Passport copy',
            'doc_type' => 'passport',
            'path' => '/files/passport.pdf',
        ];

        $file = (new CustomerFile())->fromArray($data);

        $this->assertEquals('Passport copy', $file->description);
        $this->assertEquals('passport', $file->docType);
        $this->assertEquals('/files/passport.pdf', $file->path);
    }

    public function testCustomerTagFromArray(): void
    {
        $data = [
            'key' => 'category',
            'value' => 'premium',
        ];

        $tag = (new CustomerTag())->fromArray($data);

        $this->assertEquals('category', $tag->key);
        $this->assertEquals('premium', $tag->value);
    }

    public function testContactClientIpListFromArray(): void
    {
        $data = [
            'allow' => ['192.168.1.1', '10.0.0.1'],
            'deny' => ['172.16.0.1'],
        ];

        $ipList = (new ContactClientIpList())->fromArray($data);

        $this->assertIsArray($ipList->allow);
        $this->assertCount(2, $ipList->allow);
        $this->assertIsArray($ipList->deny);
        $this->assertCount(1, $ipList->deny);
    }

    public function testEmptyContactAddress(): void
    {
        $address = new ContactAddress();

        $this->assertNull($address->street);
        $this->assertNull($address->city);
        $this->assertNull($address->country);
    }

    public function testEmptyContactPhone(): void
    {
        $phone = new ContactPhone();

        $this->assertNull($phone->countryCode);
        $this->assertNull($phone->subscriberNumber);
    }

    public function testContactAddressWithAllFields(): void
    {
        $data = [
            'street' => 'Full Address',
            'number' => '42',
            'suffix' => 'A',
            'city' => 'Amsterdam',
            'state' => 'NH',
            'zipcode' => '1012AB',
            'country' => 'NL',
        ];

        $address = (new ContactAddress())->fromArray($data);

        $this->assertEquals('Full Address', $address->street);
        $this->assertEquals('Amsterdam', $address->city);
        $this->assertEquals('NL', $address->country);
    }
}
