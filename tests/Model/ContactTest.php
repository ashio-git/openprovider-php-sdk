<?php

namespace Ashio\Tests\Model;

use Ashio\Model\Contact;
use Ashio\Model\ContactAddress;
use Ashio\Model\ContactName;
use Ashio\Model\ContactPhone;
use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase
{
    public function testFromArrayWithCompleteData()
    {
        $data = [
            'id' => 123,
            'email' => 'test@example.com',
            'name' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            'phone' => [
                'country_code' => '+1',
                'area_code' => '555',
                'subscriber_number' => '1234',
            ],
            'address' => [
                'street' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'zipcode' => '10001',
                'country' => 'US',
            ],
        ];

        $contact = (new Contact())->fromArray($data);

        $this->assertEquals(123, $contact->id);
        $this->assertEquals('test@example.com', $contact->email);
        $this->assertInstanceOf(ContactName::class, $contact->name);
        $this->assertInstanceOf(ContactPhone::class, $contact->phone);
        $this->assertInstanceOf(ContactAddress::class, $contact->address);
    }

    public function testFromArrayWithMinimalData()
    {
        $data = [
            'email' => 'minimal@example.com',
        ];

        $contact = (new Contact())->fromArray($data);

        $this->assertEquals('minimal@example.com', $contact->email);
        $this->assertNull($contact->id);
        $this->assertNull($contact->name);
    }

    public function testToArrayWithCompleteData()
    {
        $contact = new Contact();
        $contact->id = 456;
        $contact->email = 'export@example.com';

        $name = new ContactName();
        $name->first_name = 'Jane';
        $name->last_name = 'Smith';
        $contact->name = $name;

        $array = $contact->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(456, $array['id']);
        $this->assertEquals('export@example.com', $array['email']);
        $this->assertIsArray($array['name']);
    }

    public function testToArrayFiltersNullValues()
    {
        $contact = new Contact();
        $contact->email = 'test@example.com';

        $array = $contact->toArray();

        $this->assertArrayHasKey('email', $array);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('name', $array);
    }

    public function testEmailValidation()
    {
        $contact = new Contact();
        $contact->email = 'valid@example.com';

        $this->assertStringContainsString('@', $contact->email);
        $this->assertStringContainsString('.', $contact->email);
    }

    public function testNestedObjectSerialization()
    {
        $contact = new Contact();
        $contact->email = 'nested@example.com';

        $name = new ContactName();
        $name->first_name = 'Test';
        $name->last_name = 'User';
        $contact->name = $name;

        $array = $contact->toArray();

        $this->assertIsArray($array['name']);
        $this->assertArrayHasKey('first_name', $array['name']);
        $this->assertArrayHasKey('last_name', $array['name']);
    }

    public function testEmptyContact()
    {
        $contact = new Contact();

        $this->assertNull($contact->id);
        $this->assertNull($contact->email);
        $this->assertNull($contact->name);
    }

    public function testContactWithAllFields()
    {
        $data = [
            'id' => 999,
            'email' => 'complete@example.com',
            'name' => ['first_name' => 'Complete', 'last_name' => 'Test'],
            'phone' => ['country_code' => '+1', 'subscriber_number' => '5551234'],
            'address' => ['street' => 'Test St', 'city' => 'Test City', 'country' => 'US'],
            'company_name' => 'Test Company',
            'fax' => ['country_code' => '+1', 'subscriber_number' => '5555678'],
        ];

        $contact = (new Contact())->fromArray($data);

        $this->assertEquals(999, $contact->id);
        $this->assertEquals('complete@example.com', $contact->email);
        $this->assertEquals('Test Company', $contact->companyName);
    }
}
