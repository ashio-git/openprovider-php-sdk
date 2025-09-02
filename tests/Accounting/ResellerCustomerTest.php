<?php

namespace Vexura\Tests\Accounting;

use PHPUnit\Framework\TestCase;
use Vexura\OpenProvider;
use Vexura\Accounting\ResellerCustomer;
use Vexura\Model\Reseller as ResellerModel;

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
                ]
            ]);

        $resellerCustomer = new ResellerCustomer($mockApi);
        $result = $resellerCustomer->getReseller();

        $this->assertInstanceOf(ResellerModel::class, $result);
        $this->assertEquals(123, $result->id);
        $this->assertEquals('Test Reseller', $result->companyName);
    }
}
