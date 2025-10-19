<?php

namespace Ashio\Tests\Model;

use Ashio\Model\DomainCreateRequest;
use Ashio\Model\DomainUpdateRequest;
use Ashio\Model\DomainName;
use Ashio\Model\RenewDomainRequest;
use Ashio\Model\DomainRestoreRequest;
use Ashio\Model\CheckDomainResult;
use Ashio\Model\AuthCode;
use PHPUnit\Framework\TestCase;

class RequestResponseTest extends TestCase
{
    public function testDomainCreateRequest()
    {
        $domainName = (new DomainName())->fromArray(['name' => 'test', 'extension' => 'com']);
        $request = new DomainCreateRequest('HANDLE123', $domainName, 1);
        
        $array = $request->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('HANDLE123', $array['owner_handle']);
        $this->assertIsArray($array['domain']);
        $this->assertEquals(1, $array['period']);
    }

    public function testDomainUpdateRequest()
    {
        $request = new DomainUpdateRequest();
        $request->autorenew = 'on';
        
        $array = $request->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('autorenew', $array);
    }

    public function testRenewDomainRequest()
    {
        $request = new RenewDomainRequest();
        $request->period = 2;
        
        $array = $request->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals(2, $array['period']);
    }

    public function testDomainRestoreRequest()
    {
        $request = new DomainRestoreRequest();
        
        $this->assertInstanceOf(DomainRestoreRequest::class, $request);
    }

    public function testCheckDomainResult()
    {
        $data = [
            'domain' => 'example.com',
            'status' => 'free',
        ];
        
        $result = (new CheckDomainResult())->fromArray($data);
        
        $this->assertEquals('example.com', $result->domain);
        $this->assertEquals('free', $result->status);
    }

    public function testAuthCode()
    {
        $data = [
            'auth_code' => 'ABC123XYZ',
            'success' => true,
            'type' => 'external',
        ];
        
        $authCode = (new AuthCode())->fromArray($data);
        
        $this->assertEquals('ABC123XYZ', $authCode->auth_code);
        $this->assertTrue($authCode->success);
        $this->assertEquals('external', $authCode->type);
    }

    public function testAuthCodeWithMissingData()
    {
        $data = ['auth_code' => 'TEST123'];
        
        $authCode = (new AuthCode())->fromArray($data);
        
        $this->assertEquals('TEST123', $authCode->auth_code);
        $this->assertNull($authCode->success);
    }

    public function testCheckDomainResultWithStatus()
    {
        $data = [
            'domain' => 'premium.com',
            'status' => 'active',
        ];
        
        $result = (new CheckDomainResult())->fromArray($data);
        
        $this->assertEquals('active', $result->status);
    }

    public function testDomainCreateRequestWithOptionalFields()
    {
        $domainName = (new DomainName())->fromArray(['name' => 'test', 'extension' => 'com']);
        $request = new DomainCreateRequest('HANDLE123', $domainName, 1);
        
        $array = $request->toArray();
        
        $this->assertArrayHasKey('owner_handle', $array);
        $this->assertArrayHasKey('domain', $array);
    }

    public function testDomainUpdateRequestEmpty()
    {
        $request = new DomainUpdateRequest();
        
        $array = $request->toArray();
        
        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }
}
