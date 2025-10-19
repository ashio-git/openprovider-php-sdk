<?php

namespace Ashio\Tests\Model;

use Ashio\Model\Domain;
use Ashio\Model\PaginatedResponse;
use PHPUnit\Framework\TestCase;

class PaginatedResponseTest extends TestCase
{
    public function testConstructorWithEmptyResults()
    {
        $response = new PaginatedResponse(0, []);

        $this->assertEquals(0, $response->total);
        $this->assertIsArray($response->results);
        $this->assertCount(0, $response->results);
    }

    public function testConstructorWithResults()
    {
        $results = [
            new Domain(['id' => 1]),
            new Domain(['id' => 2]),
            new Domain(['id' => 3]),
        ];

        $response = new PaginatedResponse(3, $results);

        $this->assertEquals(3, $response->total);
        $this->assertCount(3, $response->results);
        $this->assertInstanceOf(Domain::class, $response->results[0]);
    }

    public function testConstructorWithMismatchedTotal()
    {
        $results = [new Domain(['id' => 1])];
        $response = new PaginatedResponse(10, $results);

        $this->assertEquals(10, $response->total);
        $this->assertCount(1, $response->results);
    }

    public function testResultsAreIterable()
    {
        $results = [
            new Domain(['id' => 1]),
            new Domain(['id' => 2]),
        ];

        $response = new PaginatedResponse(2, $results);

        $count = 0;
        foreach ($response->results as $result) {
            $this->assertInstanceOf(Domain::class, $result);
            $count++;
        }

        $this->assertEquals(2, $count);
    }

    public function testResultsAreAccessibleByIndex()
    {
        $results = [
            new Domain(['id' => 1]),
            new Domain(['id' => 2]),
        ];

        $response = new PaginatedResponse(2, $results);

        $this->assertInstanceOf(Domain::class, $response->results[0]);
        $this->assertInstanceOf(Domain::class, $response->results[1]);
    }

    public function testEmptyResponseHasZeroTotal()
    {
        $response = new PaginatedResponse(0, []);

        $this->assertEquals(0, $response->total);
        $this->assertEmpty($response->results);
    }

    public function testLargeResultSet()
    {
        $results = [];
        for ($i = 0; $i < 100; $i++) {
            $results[] = new Domain(['id' => $i]);
        }

        $response = new PaginatedResponse(100, $results);

        $this->assertEquals(100, $response->total);
        $this->assertCount(100, $response->results);
    }
}
