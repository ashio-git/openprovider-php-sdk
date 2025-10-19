<?php

namespace Ashio\Tests\Model;

use Ashio\Model\CsrCreateRequest;
use Ashio\Model\CsrCreateResponse;
use Ashio\Model\CsrDecodeRequest;
use Ashio\Model\CsrDecodeResponse;
use Ashio\Model\ApproverEmailUpdateRequest;
use PHPUnit\Framework\TestCase;

class CsrModelsTest extends TestCase
{
    public function testCsrCreateRequestToArray(): void
    {
        $request = new CsrCreateRequest('example.com', 2048, 'US');

        $array = $request->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('example.com', $array['common_name']);
        $this->assertEquals('US', $array['country']);
        $this->assertEquals(2048, $array['bits']);
    }

    public function testCsrCreateResponseFromArray(): void
    {
        $data = [
            'csr' => '-----BEGIN CERTIFICATE REQUEST-----',
            'key' => '-----BEGIN PRIVATE KEY-----',
            'command' => 'openssl req',
        ];

        $response = (new CsrCreateResponse())->fromArray($data);

        $this->assertEquals('-----BEGIN CERTIFICATE REQUEST-----', $response->csr);
        $this->assertEquals('-----BEGIN PRIVATE KEY-----', $response->key);
        $this->assertEquals('openssl req', $response->command);
    }

    public function testCsrDecodeRequestToArray(): void
    {
        $request = new CsrDecodeRequest('-----BEGIN CERTIFICATE REQUEST-----');

        $array = $request->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('-----BEGIN CERTIFICATE REQUEST-----', $array['csr']);
    }

    public function testCsrDecodeResponseFromArray(): void
    {
        $data = [
            'domain_names_count' => 1,
            'subject' => [
                'common_name' => 'example.com',
                'organization' => 'Example Inc',
            ],
        ];

        $response = (new CsrDecodeResponse())->fromArray($data);

        $this->assertEquals(1, $response->domain_names_count);
        $this->assertInstanceOf(\Ashio\Model\CsrSubject::class, $response->subject);
        $this->assertEquals('example.com', $response->subject->common_name);
        $this->assertEquals('Example Inc', $response->subject->organization);
    }

    public function testApproverEmailUpdateRequestToArray(): void
    {
        $request = new ApproverEmailUpdateRequest('admin@example.com');

        $array = $request->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('admin@example.com', $array['approver_email']);
    }

    public function testCsrCreateRequestWithMinimalParams(): void
    {
        $request = new CsrCreateRequest('test.com');

        $this->assertEquals('test.com', $request->common_name);
        $this->assertNull($request->country);
        $this->assertNull($request->bits);
    }

    public function testEmptyCsrCreateResponse(): void
    {
        $response = new CsrCreateResponse();

        $this->assertNull($response->csr);
        $this->assertNull($response->key);
        $this->assertNull($response->command);
    }
}
