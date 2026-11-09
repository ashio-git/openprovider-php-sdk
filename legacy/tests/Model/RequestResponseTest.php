<?php

namespace Ashio\Tests\Model;

use Ashio\Model\AuthCode;
use Ashio\Model\CheckDomainResult;
use Ashio\Model\DomainCreateRequest;
use Ashio\Model\DomainName;
use Ashio\Model\DomainRestoreRequest;
use Ashio\Model\DomainUpdateRequest;
use Ashio\Model\RenewDomainRequest;
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
        $domainName = (new DomainName())->fromArray(['name' => 'example', 'extension' => 'com']);
        $request = new RenewDomainRequest(domain: $domainName, id: 9876, period: 2);

        $this->assertSame($domainName, $request->domain);
        $this->assertSame(9876, $request->id);
        $this->assertSame(2, $request->period);

        $array = $request->toArray();

        $this->assertSame([
            'domain' => ['name' => 'example', 'extension' => 'com'],
            'id' => 9876,
            'period' => 2,
        ], $array);

        $parsed = RenewDomainRequest::fromArray($array);
        $this->assertInstanceOf(DomainName::class, $parsed->domain);
        $this->assertSame('example', $parsed->domain->name);
        $this->assertSame('com', $parsed->domain->extension);
        $this->assertSame($array, $parsed->toArray());

        $this->assertSame(['period' => 1], (new RenewDomainRequest(period: 1))->toArray());
    }

    public function testDomainRestoreRequest()
    {
        $domainName = (new DomainName())->fromArray(['name' => 'example', 'extension' => 'net']);
        $request = new DomainRestoreRequest($domainName, 4321);

        $this->assertSame($domainName, $request->domain);
        $this->assertSame(4321, $request->id);
        $this->assertSame([
            'domain' => ['name' => 'example', 'extension' => 'net'],
            'id' => 4321,
        ], $request->toArray());
        $this->assertSame([], (new DomainRestoreRequest())->toArray());
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
