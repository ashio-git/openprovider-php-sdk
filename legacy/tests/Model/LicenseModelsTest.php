<?php

namespace Ashio\Tests\Model;

use Ashio\Model\CreatePleskLicenseRequest;
use Ashio\Model\CreateVirtuozzoLicenseRequest;
use Ashio\Model\License;
use Ashio\Model\LicenseItem;
use Ashio\Model\LicenseKey;
use Ashio\Model\LicenseResetHwidRequest;
use Ashio\Model\UpdatePleskLicenseRequest;
use Ashio\Model\UpdateVirtuozzoLicenseRequest;
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

        $licenseKey = LicenseKey::fromArray($data);

        $this->assertSame('ABC-123-XYZ-789', $licenseKey->key);
        $this->assertSame($data, $licenseKey->toArray());
    }

    public function testLicenseItemConstruction()
    {
        $item = new LicenseItem(
            item: 'PLESK-12-WEB-HOST',
            product: 'plesk',
            sku_values: ['domains' => '30'],
            title: 'Test License',
        );

        $this->assertSame('PLESK-12-WEB-HOST', $item->item);
        $this->assertSame('plesk', $item->product);
        $this->assertSame(['domains' => '30'], $item->sku_values);
        $this->assertSame('Test License', $item->title);

        $data = [
            'item' => 'PLESK-12-WEB-HOST',
            'product' => 'plesk',
            'sku_values' => ['domains' => '30'],
            'title' => 'Test License',
        ];
        $this->assertSame($data, $item->toArray());
        $this->assertEquals($item, LicenseItem::fromArray($data));
    }

    public function testCreatePleskLicenseRequest()
    {
        $request = new CreatePleskLicenseRequest(
            attached_keys: ['KAV-1'],
            comment: 'Web server',
            ip_address_binding: '192.0.2.10',
            items: ['PLESK-12-WEB-ADMIN'],
            parent_key_id: 100,
            period: 1,
            restrict_ip_binding: false,
            title: 'New Plesk License',
        );

        $this->assertSame([
            'attached_keys' => ['KAV-1'],
            'comment' => 'Web server',
            'ip_address_binding' => '192.0.2.10',
            'items' => ['PLESK-12-WEB-ADMIN'],
            'parent_key_id' => 100,
            'period' => 1,
            'restrict_ip_binding' => false,
            'title' => 'New Plesk License',
        ], $request->toArray());
        $this->assertSame(['title' => 'Only title'], (new CreatePleskLicenseRequest(title: 'Only title'))->toArray());
    }

    public function testUpdatePleskLicenseRequest()
    {
        $request = UpdatePleskLicenseRequest::fromArray([
            'key_id' => 555,
            'suspended' => true,
            'title' => 'Updated License',
        ]);

        $this->assertSame(555, $request->key_id);
        $this->assertTrue($request->suspended);
        $this->assertSame('Updated License', $request->title);
        $this->assertNull($request->period);
        $this->assertSame([
            'key_id' => 555,
            'suspended' => true,
            'title' => 'Updated License',
        ], $request->toArray());
    }

    public function testCreateVirtuozzoLicenseRequest()
    {
        $request = new CreateVirtuozzoLicenseRequest(
            items: ['VZ-CONTAINERS-10'],
            pcs_capacity: 4,
            period: 12,
            title: 'Virtuozzo License',
        );

        $this->assertSame(4, $request->pcs_capacity);
        $this->assertSame([
            'items' => ['VZ-CONTAINERS-10'],
            'pcs_capacity' => 4,
            'period' => 12,
            'title' => 'Virtuozzo License',
        ], $request->toArray());
    }

    public function testUpdateVirtuozzoLicenseRequest()
    {
        $data = [
            'key_id' => 777,
            'pcs_capacity' => 8,
            'restrict_ip_binding' => true,
            'suspended' => false,
            'title' => 'Updated Virtuozzo',
        ];

        $request = UpdateVirtuozzoLicenseRequest::fromArray($data);

        $this->assertSame(777, $request->key_id);
        $this->assertSame(8, $request->pcs_capacity);
        $this->assertTrue($request->restrict_ip_binding);
        $this->assertFalse($request->suspended);
        $this->assertSame('Updated Virtuozzo', $request->title);
        $this->assertSame($data, $request->toArray());
    }

    public function testLicenseResetHwidRequest()
    {
        $request = new LicenseResetHwidRequest();
        $this->assertSame([], $request->toArray());

        $request->keyId = 321;
        $request->product = 'plesk';

        $this->assertSame(['key_id' => 321, 'product' => 'plesk'], $request->toArray());
    }

    public function testLicenseWithAllFields()
    {
        $data = [
            'key_id' => 789,
            'key_number' => 'VIRT-789-012',
            'product' => 'Virtuozzo',
            'status' => 'suspended',
            'title' => 'Production License',
            'order_date' => '2024-02-01 00:00:00',
            'expiration_date' => '2025-02-01 00:00:00',
            'period' => 12,
            'ip_address_binding' => '198.51.100.7',
            'items' => ['VZ-CONTAINERS-10'],
        ];

        $license = new License($data);

        $this->assertSame(789, $license->key_id);
        $this->assertSame('VIRT-789-012', $license->key_number);
        $this->assertSame('Virtuozzo', $license->product);
        $this->assertSame('suspended', $license->status);
        $this->assertSame('Production License', $license->title);
        $this->assertSame('2024-02-01 00:00:00', $license->order_date);
        $this->assertSame('2025-02-01 00:00:00', $license->expiration_date);
        $this->assertSame(12, $license->period);
        $this->assertSame('198.51.100.7', $license->ip_address_binding);
        $this->assertSame(['VZ-CONTAINERS-10'], $license->items);
        $this->assertSame($data, $license->toArray());
    }

    public function testEmptyLicense()
    {
        $license = new License([]);

        $this->assertNull($license->key_id);
        $this->assertNull($license->product);
        $this->assertSame([], $license->toArray());
    }

    public function testEmptyLicenseKey()
    {
        $licenseKey = new LicenseKey();

        $this->assertNull($licenseKey->key);
        $this->assertSame([], $licenseKey->toArray());
    }

    public function testLicenseItemWithMinimalData()
    {
        $data = ['title' => 'Minimal'];

        $item = LicenseItem::fromArray($data);

        $this->assertSame('Minimal', $item->title);
        $this->assertNull($item->item);
        $this->assertSame($data, $item->toArray());
    }
}
