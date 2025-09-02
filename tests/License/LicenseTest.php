<?php

namespace Ashio\Tests\License;

use PHPUnit\Framework\TestCase;
use Ashio\OpenProvider;
use Ashio\License\License;
use Ashio\Model\License as LicenseModel;

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
                ]
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
                    ]
                ]
            ]);

        $license = new License($mockApi);
        $result = $license->listLicenses();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(2, $result->total);
        $this->assertCount(2, $result->results);
        $this->assertInstanceOf(LicenseModel::class, $result->results[0]);
        $this->assertEquals(123, $result->results[0]->key_id);
    }
}
