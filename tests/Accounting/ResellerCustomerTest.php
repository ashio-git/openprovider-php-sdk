<?php

namespace Ashio\Tests\Accounting;

use Ashio\Accounting\ResellerCustomer;
use Ashio\Model\Contact;
use Ashio\Model\Customer as CustomerModel;
use Ashio\Model\Reseller as ResellerModel;
use Ashio\Model\ResellerSettings;
use Ashio\Model\ResellerStatistics;
use Ashio\Model\Tag;
use Ashio\OpenProvider;
use PHPUnit\Framework\TestCase;

class ResellerCustomerTest extends TestCase
{
    public function testGetReseller()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'id' => 123,
                    'company_name' => 'Test Reseller',
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->getReseller();

        $this->assertInstanceOf(ResellerModel::class, $result);
        $this->assertEquals(123, $result->id);
        $this->assertEquals('Test Reseller', $result->companyName);
    }

    public function testUpdateReseller()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('put')
            ->willReturn([
                'data' => [
                    'success' => true,
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $reseller = new ResellerModel();
        $reseller->companyName = 'Updated Company';
        $result = $resellerCustomer->updateReseller(123, $reseller);

        $this->assertTrue($result);
    }

    public function testGetSettings()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'brand_name' => 'Test Brand',
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->getSettings();

        $this->assertInstanceOf(ResellerSettings::class, $result);
    }

    public function testGetStatistics()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'domains' => 100,
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->getStatistics();

        $this->assertInstanceOf(ResellerStatistics::class, $result);
    }

    public function testListContacts()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 2,
                    'results' => [
                        ['id' => 1, 'email' => 'test1@example.com'],
                        ['id' => 2, 'email' => 'test2@example.com'],
                    ],
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->listContacts();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(2, $result->total);
        $this->assertCount(2, $result->results);
    }

    public function testCreateContact()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->expects($this->once())
            ->method('post')
            ->willReturn(['data' => ['id' => 123]]);
        $mockApi->expects($this->once())
            ->method('get')
            ->willReturn(['data' => ['id' => 123, 'email' => 'new@example.com']]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $contact = new Contact();
        $contact->email = 'new@example.com';
        $result = $resellerCustomer->createContact($contact);

        $this->assertInstanceOf(Contact::class, $result);
    }

    public function testGetContact()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'id' => 123,
                    'email' => 'test@example.com',
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->getContact(123);

        $this->assertInstanceOf(Contact::class, $result);
    }

    public function testUpdateContact()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('put')
            ->willReturn([
                'data' => [
                    'success' => true,
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $contact = new Contact();
        $result = $resellerCustomer->updateContact(123, $contact);

        $this->assertTrue($result);
    }

    public function testDeleteContact()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('delete')
            ->willReturn([
                'data' => [
                    'success' => true,
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->deleteContact(123);

        $this->assertTrue($result);
    }

    public function testListCustomers()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['handle' => 'CUST123', 'email' => 'customer@example.com'],
                    ],
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->listCustomers();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
    }

    public function testCreateCustomer()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->expects($this->once())
            ->method('post')
            ->willReturn(['data' => ['handle' => 'CUST123']]);
        $mockApi->expects($this->once())
            ->method('get')
            ->willReturn(['data' => ['handle' => 'CUST123', 'email' => 'new@example.com']]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $customer = new CustomerModel();
        $result = $resellerCustomer->createCustomer($customer);

        $this->assertInstanceOf(CustomerModel::class, $result);
    }

    public function testGetCustomer()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'handle' => 'CUST123',
                    'email' => 'test@example.com',
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->getCustomer('CUST123');

        $this->assertInstanceOf(CustomerModel::class, $result);
    }

    public function testUpdateCustomer()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('put')
            ->willReturn([
                'data' => [
                    'success' => true,
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $customer = new CustomerModel();
        $result = $resellerCustomer->updateCustomer('CUST123', $customer);

        $this->assertTrue($result);
    }

    public function testDeleteCustomer()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('delete')
            ->willReturn([
                'data' => [
                    'success' => true,
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->deleteCustomer('CUST123');

        $this->assertTrue($result);
    }

    public function testListTags()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['key' => 'tag1', 'value' => 'value1'],
                    ],
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->listTags();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
    }

    public function testCreateTag()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn([
                'data' => [
                    'success' => true,
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $tag = new Tag();
        $result = $resellerCustomer->createTag($tag);

        $this->assertTrue($result);
    }

    public function testDeleteTag()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('delete')
            ->willReturn([
                'data' => [
                    'success' => true,
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->deleteTag('tag1');

        $this->assertTrue($result);
    }

    public function testListDomainEmailVerifications()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['email' => 'test@example.com', 'status' => 'verified'],
                    ],
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->listDomainEmailVerifications();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
    }

    public function testRestartEmailVerification()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn([
                'data' => [
                    'success' => true,
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->restartEmailVerification('test@example.com');

        $this->assertTrue($result);
    }

    public function testStartEmailVerification()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn([
                'data' => [
                    'id' => 123,
                ],
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->startEmailVerification('test@example.com');

        $this->assertEquals(123, $result);
    }
}
