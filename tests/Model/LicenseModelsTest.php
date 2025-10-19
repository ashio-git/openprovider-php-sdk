<?php

namespace Ashio\Tests\Model;

use Ashio\Model\License;
use Ashio\Model\LicenseKey;
use Ashio\Model\LicenseItem;
use Ashio\Model\CreatePleskLicenseRequest;
use Ashio\Model\UpdatePleskLicenseRequest;
use Ashio\Model\CreateVirtuozzoLicenseRequest;
use Ashio\Model\UpdateVirtuozzoLicenseRequest;
use Ashio\Model\LicenseResetHwidRequest;
use PHPUnit\Framework\TestCase;

class LicenseModelsTest extends TestCase
{
    public function testLicenseFromArray()
    {
        $data = [
            'key_id' => 123,
            'product' => 'Plesk',
            'status' => 'active',
            'key_number' => 'PLSK-123-456',
        ];
        
        $license = new License($data);
        
        $this->assertEquals(123, $license->key_id);
        $this->assertEquals('Plesk', $license->product);
        $this->assertEquals('active', $license->status);
        $this->assertEquals('PLSK-123-456', $license->key_number);
    }

    public function testLicenseKeyFromArray()
    {
        $data = [
            'key' => 'ABC-123-XYZ-789',
        ];
        
        $licenseKey = (new LicenseKey())->fromArray($data);
        
        $this->assertEquals('ABC-123-XYZ-789', $licenseKey->key);
    }

    public function testLicenseItemConstruction()
    {
        $item = new LicenseItem();
        $item->title = 'Test License';
        $item->status = 'active';
        
        $this->assertEquals('Test License', $item->title);
        $this->assertEquals('active', $item->status);
    }

    public function testCreatePleskLicenseRequest()
    {
        $request = new CreatePleskLicenseRequest();
        $request->title = 'New Plesk License';
        
        $array = $request->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('title', $array);
    }

    public function testUpdatePleskLicenseRequest()
    {
        $request = new UpdatePleskLicenseRequest();
        $request->title = 'Updated License';
        
        $array = $request->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('Updated License', $array['title']);
    }

    public function testCreateVirtuozzoLicenseRequest()
    {
        $request = new CreateVirtuozzoLicenseRequest();
        $request->title = 'Virtuozzo License';
        
        $array = $request->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('title', $array);
    }

    public function testUpdateVirtuozzoLicenseRequest()
    {
        $request = new UpdateVirtuozzoLicenseRequest();
        $request->title = 'Updated Virtuozzo';
        
        $array = $request->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('Updated Virtuozzo', $array['title']);
    }

    public function testLicenseResetHwidRequest()
    {
        $request = new LicenseResetHwidRequest();
        
        $this->assertInstanceOf(LicenseResetHwidRequest::class, $request);
    }

    public function testLicenseWithAllFields()
    {
        $data = [
            'key_id' => 789,
            'product' => 'Virtuozzo',
            'status' => 'suspended',
            'key_number' => 'VIRT-789-012',
            'title' => 'Production License',
            'parent_key_id' => 100,
        ];
        
        $license = new License($data);
        
        $this->assertEquals(789, $license->key_id);
        $this->assertEquals('Virtuozzo', $license->product);
        $this->assertEquals('suspended', $license->status);
        $this->assertEquals('Production License', $license->title);
    }

    public function testEmptyLicense()
    {
        $license = new License([]);
        
        $this->assertNull($license->key_id);
        $this->assertNull($license->product);
    }

    public function testEmptyLicenseKey()
    {
        $licenseKey = new LicenseKey();
        
        $this->assertNull($licenseKey->key);
    }

    public function testLicenseItemWithMinimalData()
    {
        $data = ['title' => 'Minimal'];
        
        $item = (new LicenseItem())->fromArray($data);
        
        $this->assertEquals('Minimal', $item->title);
    }
}
