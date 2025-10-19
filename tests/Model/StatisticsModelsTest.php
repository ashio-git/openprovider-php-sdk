<?php

namespace Ashio\Tests\Model;

use Ashio\Model\StatisticsCustomerStatistics;
use Ashio\Model\StatisticsDomainStatisticsByStatus;
use Ashio\Model\StatisticsSpamExpertsStatistics;
use PHPUnit\Framework\TestCase;

class StatisticsModelsTest extends TestCase
{
    public function testStatisticsCustomerStatisticsFromArray(): void
    {
        $data = [
            'total' => 500,
            'modified' => '2024-01-01',
        ];

        $stats = (new StatisticsCustomerStatistics())->fromArray($data);

        $this->assertEquals(500, $stats->total);
        $this->assertEquals('2024-01-01', $stats->modified);
    }

    public function testStatisticsDomainStatisticsByStatusFromArray(): void
    {
        $data = [
            'ACT' => 100,
            'PEN' => 10,
            'FAI' => 5,
        ];

        $stats = (new StatisticsDomainStatisticsByStatus())->fromArray($data);

        $this->assertEquals(100, $stats->ACT);
        $this->assertEquals(10, $stats->PEN);
        $this->assertEquals(5, $stats->FAI);
    }

    public function testStatisticsSpamExpertsStatisticsFromArray(): void
    {
        $data = [
            'incoming_count' => 250,
            'outgoing_count' => 200,
            'status' => 'active',
        ];

        $stats = (new StatisticsSpamExpertsStatistics())->fromArray($data);

        $this->assertEquals(250, $stats->incomingCount);
        $this->assertEquals(200, $stats->outgoingCount);
        $this->assertEquals('active', $stats->status);
    }

    public function testEmptyStatisticsCustomerStatistics(): void
    {
        $stats = new StatisticsCustomerStatistics();

        $this->assertNull($stats->total);
        $this->assertNull($stats->modified);
    }

    public function testEmptyStatisticsDomainStatisticsByStatus(): void
    {
        $stats = new StatisticsDomainStatisticsByStatus();

        $this->assertNull($stats->ACT);
        $this->assertNull($stats->PEN);
    }

    public function testEmptyStatisticsSpamExpertsStatistics(): void
    {
        $stats = new StatisticsSpamExpertsStatistics();

        $this->assertNull($stats->incomingCount);
        $this->assertNull($stats->status);
    }

    public function testStatisticsCustomerStatisticsToArray(): void
    {
        $stats = new StatisticsCustomerStatistics();
        $stats->total = 300;
        $stats->modified = '2024-02-01';

        $array = $stats->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(300, $array['total']);
        $this->assertEquals('2024-02-01', $array['modified']);
    }
}
