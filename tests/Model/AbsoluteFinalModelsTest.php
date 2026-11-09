<?php

namespace Ashio\Tests\Model;

use Ashio\Model\DomainName;
use Ashio\Model\DomainTradeRequest;
use Ashio\Model\DomainTransferRequest;
use Ashio\Model\EmailTemplateCreateRequest;
use Ashio\Model\EmailTemplateUpdateRequest;
use PHPUnit\Framework\TestCase;

class AbsoluteFinalModelsTest extends TestCase
{
    public function testDomainTradeRequestConstructor(): void
    {
        $domainName = new DomainName('example', 'com');
        $request = new DomainTradeRequest($domainName);

        $this->assertInstanceOf(DomainName::class, $request->domain);
    }

    public function testDomainTransferRequestConstructor(): void
    {
        $domainName = new DomainName('example', 'com');
        $request = new DomainTransferRequest($domainName);

        $this->assertInstanceOf(DomainName::class, $request->domain);
    }

    public function testDomainTransferRequestToArray(): void
    {
        $domainName = new DomainName('example', 'com');
        $request = new DomainTransferRequest($domainName);
        $request->authCode = 'AUTH123';

        $array = $request->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('domain', $array);
        $this->assertEquals('AUTH123', $request->authCode);
    }

    public function testEmailTemplateCreateRequestConstructor(): void
    {
        $request = new EmailTemplateCreateRequest('welcome', 'Welcome Template');

        $this->assertEquals('welcome', $request->group);
        $this->assertEquals('Welcome Template', $request->name);
    }

    public function testEmailTemplateCreateRequestToArray(): void
    {
        $request = new EmailTemplateCreateRequest('notification', 'Notification Template');
        $request->isActive = true;

        $array = $request->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('notification', $array['group']);
        $this->assertEquals('Notification Template', $array['name']);
        $this->assertTrue($array['is_active']);
    }

    public function testEmailTemplateUpdateRequestConstructor(): void
    {
        $request = new EmailTemplateUpdateRequest();
        $request->name = 'Updated Template';
        $request->group = 'notifications';

        $this->assertEquals('Updated Template', $request->name);
        $this->assertEquals('notifications', $request->group);
    }

    public function testEmailTemplateUpdateRequestToArray(): void
    {
        $request = new EmailTemplateUpdateRequest();
        $request->name = 'Test Template';
        $request->isActive = false;

        $array = $request->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Test Template', $array['name']);
        $this->assertFalse($array['is_active']);
    }
}
