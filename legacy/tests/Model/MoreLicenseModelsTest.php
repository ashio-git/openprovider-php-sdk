<?php

namespace Ashio\Tests\Model;

use Ashio\Model\LicenseItem;
use PHPUnit\Framework\TestCase;

class MoreLicenseModelsTest extends TestCase
{
    public function testLicenseItemFromArray(): void
    {
        $data = [
            'item' => 'license-item-1',
            'product' => 'Plesk',
            'title' => 'Plesk License',
        ];

        $item = (new LicenseItem())->fromArray($data);

        $this->assertEquals('license-item-1', $item->item);
        $this->assertEquals('Plesk', $item->product);
        $this->assertEquals('Plesk License', $item->title);
    }

    public function testEmptyLicenseItem(): void
    {
        $item = new LicenseItem();

        $this->assertNull($item->item);
        $this->assertNull($item->product);
        $this->assertNull($item->title);
    }
}
