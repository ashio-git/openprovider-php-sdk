<?php

namespace Ashio\Tests\Model;

use Ashio\Model\RenewDomainResponse;
use Ashio\Model\RestoreDomainResponse;
use Ashio\Model\TradeDomainResponse;
use Ashio\Model\TransferDomainResponse;
use Ashio\Model\SuggestNameDomainResult;
use PHPUnit\Framework\TestCase;

class DomainResponseModelsTest extends TestCase
{
    public function testRenewDomainResponseConstructor(): void
    {
        $response = new RenewDomainResponse();
        $response->success = true;
        $response->domain = 'example.com';

        $this->assertTrue($response->success);
        $this->assertEquals('example.com', $response->domain);
    }

    public function testRestoreDomainResponseConstructor(): void
    {
        $response = new RestoreDomainResponse();
        $response->success = true;

        $this->assertTrue($response->success);
    }

    public function testTradeDomainResponseConstructor(): void
    {
        $response = new TradeDomainResponse();
        $response->success = true;

        $this->assertTrue($response->success);
    }

    public function testTransferDomainResponseConstructor(): void
    {
        $response = new TransferDomainResponse();
        $response->success = true;
        $response->domain = 'example.com';

        $this->assertTrue($response->success);
        $this->assertEquals('example.com', $response->domain);
    }

    public function testSuggestNameDomainResultConstructor(): void
    {
        $result = new SuggestNameDomainResult();
        $result->domain = 'example.com';
        $result->available = true;

        $this->assertEquals('example.com', $result->domain);
        $this->assertTrue($result->available);
    }

    public function testEmptyRenewDomainResponse(): void
    {
        $response = new RenewDomainResponse();

        $this->assertNull($response->success);
        $this->assertNull($response->domain);
    }
}
