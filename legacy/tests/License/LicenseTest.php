<?php

namespace Ashio\Tests\License;

use Ashio\License\License;
use Ashio\Model\License as LicenseModel;
use Ashio\OpenProvider;
use PHPUnit\Framework\TestCase;

class LicenseTest extends TestCase
{
    public function testGetPleskLicense()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'key_id' => 123,
                    'product' => 'Plesk',
                ],
            ]);

        $license = new License($mockApi);
        $result = $license->getPleskLicense(123);

        $this->assertInstanceOf(LicenseModel::class, $result);
        $this->assertEquals(123, $result->key_id);
        $this->assertEquals('Plesk', $result->product);
    }

    public function testListLicenses()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 2,
                    'results' => [
                        ['key_id' => 123, 'product' => 'Plesk'],
                        ['key_id' => 456, 'product' => 'Virtuozzo'],
                    ],
                ],
            ]);

        $license = new License($mockApi);
        $result = $license->listLicenses();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(2, $result->total);
        $this->assertCount(2, $result->results);
        $this->assertInstanceOf(LicenseModel::class, $result->results[0]);
        $this->assertEquals(123, $result->results[0]->key_id);
    }

    public function testListPleskLicenses()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['key_id' => 123, 'product' => 'Plesk'],
                    ],
                ],
            ]);

        $license = new License($mockApi);
        $result = $license->listPleskLicenses();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
    }

    public function testCreatePleskLicense()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['key_id' => 123]]);

        $license = new License($mockApi);
        $req = new \Ashio\Model\CreatePleskLicenseRequest();
        $result = $license->createPleskLicense($req);

        $this->assertInstanceOf(LicenseModel::class, $result);
    }

    public function testUpdatePleskLicense()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('put')
            ->willReturn(['data' => ['success' => true]]);

        $license = new License($mockApi);
        $req = new \Ashio\Model\UpdatePleskLicenseRequest();
        $result = $license->updatePleskLicense(123, $req);

        $this->assertTrue($result);
    }

    public function testDeletePleskLicense()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('delete')
            ->willReturn(['data' => ['success' => true]]);

        $license = new License($mockApi);
        $result = $license->deletePleskLicense(123);

        $this->assertInstanceOf(\Ashio\Model\ActionResponse::class, $result);
    }

    public function testGetPleskKey()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn(['data' => ['key' => 'ABC-123']]);

        $license = new License($mockApi);
        $result = $license->getPleskKey(123);

        $this->assertInstanceOf(\Ashio\Model\LicenseKey::class, $result);
    }

    public function testListVirtuozzoLicenses()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['key_id' => 456, 'product' => 'Virtuozzo'],
                    ],
                ],
            ]);

        $license = new License($mockApi);
        $result = $license->listVirtuozzoLicenses();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
    }

    public function testCreateVirtuozzoLicense()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['key_id' => 456]]);

        $license = new License($mockApi);
        $req = new \Ashio\Model\CreateVirtuozzoLicenseRequest();
        $result = $license->createVirtuozzoLicense($req);

        $this->assertInstanceOf(LicenseModel::class, $result);
    }

    public function testGetVirtuozzoLicense()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn(['data' => ['key_id' => 456, 'product' => 'Virtuozzo']]);

        $license = new License($mockApi);
        $result = $license->getVirtuozzoLicense(456);

        $this->assertInstanceOf(LicenseModel::class, $result);
    }

    public function testUpdateVirtuozzoLicense()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('put')
            ->willReturn(['data' => ['success' => true]]);

        $license = new License($mockApi);
        $req = new \Ashio\Model\UpdateVirtuozzoLicenseRequest();
        $result = $license->updateVirtuozzoLicense(456, $req);

        $this->assertTrue($result);
    }

    public function testDeleteVirtuozzoLicense()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('delete')
            ->willReturn(['data' => ['success' => true]]);

        $license = new License($mockApi);
        $result = $license->deleteVirtuozzoLicense(456);

        $this->assertInstanceOf(\Ashio\Model\ActionResponse::class, $result);
    }

    public function testGetVirtuozzoKey()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn(['data' => ['key' => 'XYZ-789']]);

        $license = new License($mockApi);
        $result = $license->getVirtuozzoKey(456);

        $this->assertInstanceOf(\Ashio\Model\LicenseKey::class, $result);
    }

    public function testListItems()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 1,
                    'results' => [
                        ['key_id' => 123],
                    ],
                ],
            ]);

        $license = new License($mockApi);
        $result = $license->listItems();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
    }

    public function testResetHwid()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('post')
            ->willReturn(['data' => ['success' => true]]);

        $license = new License($mockApi);
        $req = new \Ashio\Model\LicenseResetHwidRequest();
        $result = $license->resetHwid('plesk', 123, $req);

        $this->assertInstanceOf(\Ashio\Model\ActionResponse::class, $result);
    }
}
