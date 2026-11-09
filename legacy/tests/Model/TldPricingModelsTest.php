<?php

namespace Ashio\Tests\Model;

use Ashio\Model\CheckDomainResult;
use Ashio\Model\DeleteDomainResponse;
use Ashio\Model\TldPrice;
use PHPUnit\Framework\TestCase;

class TldPricingModelsTest extends TestCase
{
    public function testTldPriceConstructor(): void
    {
        $data = [
            'product' => ['name' => 'domain'],
            'reseller' => ['price' => 10.50],
        ];

        $tldPrice = new TldPrice($data);

        $this->assertIsArray($tldPrice->product);
        $this->assertIsArray($tldPrice->reseller);
    }

    public function testCheckDomainResultFromArray(): void
    {
        $data = [
            'domain' => 'example.com',
            'status' => 'free',
        ];

        $result = (new CheckDomainResult())->fromArray($data);

        $this->assertEquals('example.com', $result->domain);
        $this->assertEquals('free', $result->status);
    }

    public function testDeleteDomainResponseFromArray(): void
    {
        $data = [
            'status' => 'success',
        ];

        $response = (new DeleteDomainResponse())->fromArray($data);

        $this->assertEquals('success', $response->status);
    }

    public function testEmptyTldPrice(): void
    {
        $tldPrice = new TldPrice();

        $this->assertNull($tldPrice->product);
        $this->assertNull($tldPrice->reseller);
    }

    public function testEmptyCheckDomainResult(): void
    {
        $result = new CheckDomainResult();

        $this->assertNull($result->domain);
        $this->assertNull($result->status);
    }
}
