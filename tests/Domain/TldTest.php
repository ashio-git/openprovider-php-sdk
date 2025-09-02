<?php

namespace Ashio\Tests\Domain;

use PHPUnit\Framework\TestCase;
use Ashio\OpenProvider;
use Ashio\Domain\Domain;
use Ashio\Model\Tld;

class TldTest extends TestCase
{
    public function testGetTld()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'name' => 'com',
                    'status' => 'ACT',
                ]
            ]);

        $domain = new Domain($mockApi);
        $tld = $domain->getTld('com');

        $this->assertInstanceOf(Tld::class, $tld);
        $this->assertEquals('com', $tld->name);
        $this->assertEquals('ACT', $tld->status);
    }

    public function testListTlds()
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')
            ->willReturn([
                'data' => [
                    'total' => 2,
                    'results' => [
                        ['name' => 'com', 'status' => 'ACT'],
                        ['name' => 'net', 'status' => 'ACT'],
                    ]
                ]
            ]);

        $domain = new Domain($mockApi);
        $result = $domain->listTlds();

        $this->assertInstanceOf(\Ashio\Model\PaginatedResponse::class, $result);
        $this->assertEquals(2, $result->total);
        $this->assertCount(2, $result->results);
        $this->assertInstanceOf(Tld::class, $result->results[0]);
        $this->assertEquals('com', $result->results[0]->name);
    }
}
