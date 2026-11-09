<?php

namespace Ashio\Tests\Model;

use Ashio\Model\RenewDomainResponse;
use Ashio\Model\RestoreDomainResponse;
use Ashio\Model\SuggestNameDomainResult;
use Ashio\Model\TradeDomainResponse;
use Ashio\Model\TransferDomainResponse;
use PHPUnit\Framework\TestCase;

class DomainResponseModelsTest extends TestCase
{
    public function testRenewDomainResponseConstructor(): void
    {
        $response = (new RenewDomainResponse())->fromArray(['status' => 'ACT']);

        $this->assertSame('ACT', $response->status);
    }

    public function testRestoreDomainResponseConstructor(): void
    {
        $response = (new RestoreDomainResponse())->fromArray(['status' => 'ACT']);

        $this->assertSame('ACT', $response->status);
    }

    public function testTradeDomainResponseConstructor(): void
    {
        $response = (new TradeDomainResponse())->fromArray([
            'creation_date' => '2024-01-15 10:00:00',
            'expiration_date' => '2025-01-15 10:00:00',
            'renewal_date' => '2024-12-15 10:00:00',
            'status' => 'REQ',
        ]);

        $this->assertSame('2024-01-15 10:00:00', $response->creation_date);
        $this->assertSame('2025-01-15 10:00:00', $response->expiration_date);
        $this->assertSame('2024-12-15 10:00:00', $response->renewal_date);
        $this->assertSame('REQ', $response->status);
    }

    public function testTransferDomainResponseConstructor(): void
    {
        $response = (new TransferDomainResponse())->fromArray([
            'auth_code' => 'Xy7#kP2q',
            'description' => 'Transfer requested',
            'expiration_date' => '2025-06-01 00:00:00',
            'id' => 4242,
            'name' => 'example.com',
            'renewal_date' => '2025-05-01 00:00:00',
            'required' => true,
            'status' => 'REQ',
            'type' => 'transfer',
        ]);

        $this->assertSame('Xy7#kP2q', $response->auth_code);
        $this->assertSame('Transfer requested', $response->description);
        $this->assertNull($response->error);
        $this->assertSame('2025-06-01 00:00:00', $response->expiration_date);
        $this->assertSame(4242, $response->id);
        $this->assertSame('example.com', $response->name);
        $this->assertSame('2025-05-01 00:00:00', $response->renewal_date);
        $this->assertTrue($response->required);
        $this->assertSame('REQ', $response->status);
        $this->assertSame('transfer', $response->type);
    }

    public function testSuggestNameDomainResultConstructor(): void
    {
        $result = new SuggestNameDomainResult(domain: 'example.com', name: 'example', tld: 'com');

        $this->assertSame('example.com', $result->domain);
        $this->assertSame('example', $result->name);
        $this->assertSame('com', $result->tld);

        $data = ['domain' => 'example.com', 'name' => 'example', 'tld' => 'com'];
        $this->assertSame($data, $result->toArray());
        $this->assertEquals($result, SuggestNameDomainResult::fromArray($data));
        // Unset fields are omitted from the API payload.
        $this->assertSame(['name' => 'example'], (new SuggestNameDomainResult(name: 'example'))->toArray());
    }

    public function testEmptyRenewDomainResponse(): void
    {
        $response = new RenewDomainResponse();

        $this->assertNull($response->status);
        $this->assertNull((new RenewDomainResponse())->fromArray([])->status);
    }
}
