<?php

namespace Ashio\Tests\Model;

use Ashio\Model\EasyDmarcCreateRequest;
use Ashio\Model\EasyDmarcOrder;
use Ashio\Model\EasyDmarcRetryRequest;
use Ashio\Model\SpamExpertCreateDomainRequest;
use Ashio\Model\SpamExpertDomain;
use Ashio\Model\SpamExpertGenerateLoginUrlRequest;
use Ashio\Model\SpamExpertUpdateDomainRequest;
use PHPUnit\Framework\TestCase;

class EasyDmarcSpamExpertTest extends TestCase
{
    public function testEasyDmarcOrderFromArray()
    {
        $data = [
            'id' => 123,
            'domain' => ['name' => 'example', 'extension' => 'com'],
            'status' => 'active',
        ];

        $order = (new EasyDmarcOrder())->fromArray($data);

        $this->assertEquals(123, $order->id);
        $this->assertEquals('active', $order->status);
    }

    public function testEasyDmarcCreateRequest()
    {
        $domainName = (new \Ashio\Model\DomainName())->fromArray(['name' => 'test', 'extension' => 'com']);
        $request = new EasyDmarcCreateRequest($domainName, 'OWNER123');

        $array = $request->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('domain', $array);
        $this->assertEquals('OWNER123', $array['owner_handle']);
    }

    public function testEasyDmarcRetryRequest()
    {
        $request = new EasyDmarcRetryRequest();

        $this->assertInstanceOf(EasyDmarcRetryRequest::class, $request);
    }

    public function testSpamExpertDomainFromArray()
    {
        $data = [
            'domain_name' => 'example.com',
            'is_active' => true,
        ];

        $domain = (new SpamExpertDomain())->fromArray($data);

        $this->assertEquals('example.com', $domain->domain_name);
        $this->assertTrue($domain->is_active);
    }

    public function testSpamExpertCreateDomainRequest()
    {
        $request = new SpamExpertCreateDomainRequest('example.com');

        $array = $request->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('example.com', $array['domain_name']);
    }

    public function testSpamExpertUpdateDomainRequest()
    {
        $request = new SpamExpertUpdateDomainRequest();

        $this->assertInstanceOf(SpamExpertUpdateDomainRequest::class, $request);
    }

    public function testSpamExpertGenerateLoginUrlRequest()
    {
        $request = new SpamExpertGenerateLoginUrlRequest('example.com');
        $request->bundle = true;

        $array = $request->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('example.com', $array['domain_or_email']);
        $this->assertTrue($array['bundle']);
    }

    public function testEmptyEasyDmarcOrder()
    {
        $order = new EasyDmarcOrder();

        $this->assertNull($order->id);
        $this->assertNull($order->status);
    }

    public function testEmptySpamExpertDomain()
    {
        $domain = new SpamExpertDomain();

        $this->assertNull($domain->domain_name);
        $this->assertNull($domain->is_active);
    }

    public function testEasyDmarcOrderWithAllFields()
    {
        $data = [
            'id' => 456,
            'domain' => ['name' => 'full', 'extension' => 'com'],
            'status' => 'pending',
            'product_name' => 'premium',
        ];

        $order = (new EasyDmarcOrder())->fromArray($data);

        $this->assertEquals(456, $order->id);
        $this->assertEquals('pending', $order->status);
    }

    public function testSpamExpertDomainWithAllFields()
    {
        $data = [
            'domain_name' => 'complete.com',
            'is_active' => false,
            'products' => ['incoming' => true, 'outgoing' => false],
        ];

        $domain = (new SpamExpertDomain())->fromArray($data);

        $this->assertEquals('complete.com', $domain->domain_name);
        $this->assertFalse($domain->is_active);
    }
}
