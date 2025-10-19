<?php

namespace Ashio\Tests\Model;

use Ashio\Model\Reseller;
use Ashio\Model\ResellerAdditionalData;
use Ashio\Model\RgpStatuses;
use PHPUnit\Framework\TestCase;

class ResellerModelsTest extends TestCase
{
    public function testResellerConstructor(): void
    {
        $reseller = new Reseller();
        $reseller->id = 123;
        $reseller->companyName = 'Reseller Inc';

        $this->assertEquals(123, $reseller->id);
        $this->assertEquals('Reseller Inc', $reseller->companyName);
    }

    public function testResellerAdditionalDataConstructor(): void
    {
        $data = new ResellerAdditionalData();
        $data->field1 = 'value1';

        $this->assertEquals('value1', $data->field1);
    }

    public function testRgpStatusesConstructor(): void
    {
        $statuses = new RgpStatuses();
        $statuses->addPeriod = true;
        $statuses->autoRenewPeriod = false;

        $this->assertTrue($statuses->addPeriod);
        $this->assertFalse($statuses->autoRenewPeriod);
    }

    public function testEmptyReseller(): void
    {
        $reseller = new Reseller();

        $this->assertNull($reseller->id);
        $this->assertNull($reseller->companyName);
    }

    public function testEmptyRgpStatuses(): void
    {
        $statuses = new RgpStatuses();

        $this->assertNull($statuses->addPeriod);
        $this->assertNull($statuses->autoRenewPeriod);
    }
}
