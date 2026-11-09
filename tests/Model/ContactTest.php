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
        $this->assertSame('John', $contact->name->firstName);
        $this->assertSame('Doe', $contact->name->lastName);
        $this->assertInstanceOf(ContactPhone::class, $contact->phone);
        $this->assertSame('+1', $contact->phone->countryCode);
        $this->assertSame('555', $contact->phone->areaCode);
        $this->assertSame('1234', $contact->phone->subscriberNumber);
        $this->assertInstanceOf(ContactAddress::class, $contact->address);
        $this->assertSame('123 Main St', $contact->address->street);
        $this->assertSame('New York', $contact->address->city);
        $this->assertSame('NY', $contact->address->state);
        $this->assertSame('10001', $contact->address->zipcode);
        $this->assertSame('US', $contact->address->country);
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
        $name->firstName = 'Jane';
        $name->lastName = 'Smith';
        $contact->name = $name;

        $array = $contact->toArray();

        $this->assertSame(456, $array['id']);
        $this->assertSame('export@example.com', $array['email']);
        // ContactName::toArray() emits every API key, including unset ones.
        $this->assertSame([
            'initials' => null,
            'first_name' => 'Jane',
            'prefix' => null,
            'last_name' => 'Smith',
            'full_name' => null,
        ], $array['name']);
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

    public function testEmailRoundTrip()
    {
        $contact = (new Contact())->fromArray(['email' => 'valid@example.com']);

        $this->assertSame('valid@example.com', $contact->email);
        $this->assertSame(['email' => 'valid@example.com'], $contact->toArray());
    }

    public function testNestedObjectSerialization()
    {
        $contact = new Contact();
        $contact->email = 'nested@example.com';

        $name = new ContactName();
        $name->firstName = 'Test';
        $name->lastName = 'User';
        $contact->name = $name;

        $array = $contact->toArray();

        $this->assertIsArray($array['name']);
        $this->assertSame('Test', $array['name']['first_name']);
        $this->assertSame('User', $array['name']['last_name']);
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
            'reseller_id' => 12345,
            'username' => 'complete.user',
            'role' => 'admin',
            'vat' => 'NL123456789B01',
            'locale' => 'en_GB',
            'is_active' => true,
            'api_access_enabled' => true,
            'api_client_ip_list' => ['allow' => ['192.0.2.10'], 'deny' => ['198.51.100.0/24']],
            'auth_type' => 'two_factor',
            'last_login_at' => '2024-01-15 10:30:00',
            'additional_data' => ['birth_city' => 'Utrecht', 'birth_date' => '1980-02-01'],
        ];

        $contact = (new Contact())->fromArray($data);

        $this->assertSame(999, $contact->id);
        $this->assertSame('complete@example.com', $contact->email);
        $this->assertSame('Test Company', $contact->companyName);
        $this->assertSame(12345, $contact->resellerId);
        $this->assertSame('complete.user', $contact->username);
        $this->assertSame('admin', $contact->role);
        $this->assertSame('NL123456789B01', $contact->vat);
        $this->assertSame('en_GB', $contact->locale);
        $this->assertTrue($contact->isActive);
        $this->assertTrue($contact->apiAccessEnabled);
        $this->assertSame(['192.0.2.10'], $contact->apiClientIpList->allow);
        $this->assertSame(['198.51.100.0/24'], $contact->apiClientIpList->deny);
        $this->assertNull($contact->rcpClientIpList);
        $this->assertSame('two_factor', $contact->authType);
        $this->assertSame('2024-01-15 10:30:00', $contact->lastLoginAt);
        $this->assertSame('Utrecht', $contact->additionalData->birthCity);
        $this->assertSame('1980-02-01', $contact->additionalData->birthDate);

        $array = $contact->toArray();
        $this->assertSame(12345, $array['reseller_id']);
        $this->assertSame('admin', $array['role']);
        $this->assertTrue($array['is_active']);
        $this->assertSame($data['api_client_ip_list'], $array['api_client_ip_list']);
        $this->assertSame([
            'birth_date' => '1980-02-01',
            'birth_city' => 'Utrecht',
            'social_security_number' => null,
        ], $array['additional_data']);
        $this->assertArrayNotHasKey('rcp_client_ip_list', $array);
    }
}
