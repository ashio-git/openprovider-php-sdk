<?php

namespace Ashio\Tests\Model;

use Ashio\Model\StatisticsCurrencyStatistics;
use Ashio\Model\StatisticsDnsStatistics;
use Ashio\Model\StatisticsDomainStatistics;
use Ashio\Model\StatisticsDomainStatisticsByStatus;
use Ashio\Model\StatisticsLicenseStatistics;
use Ashio\Model\StatisticsSslStatistics;
use Ashio\Model\StatisticsSslStatisticsByStatus;
use PHPUnit\Framework\TestCase;

class StatisticsCompleteModelsTest extends TestCase
{
    public function testStatisticsCurrencyStatisticsConstructor(): void
    {
        $stats = (new StatisticsCurrencyStatistics())->fromArray(['modified' => '2024-05-01 12:00:00']);

        $this->assertSame('2024-05-01 12:00:00', $stats->modified);
        $this->assertSame(['modified' => '2024-05-01 12:00:00'], $stats->toArray());
    }

    public function testStatisticsDnsStatisticsConstructor(): void
    {
        $stats = (new StatisticsDnsStatistics())->fromArray(['total' => 100]);

        $this->assertSame(100, $stats->total);
        $this->assertSame(['total' => 100], $stats->toArray());
    }

    public function testStatisticsDomainStatisticsConstructor(): void
    {
        $stats = (new StatisticsDomainStatistics())->fromArray([
            'total' => 500,
            'by_status' => ['ACT' => 450, 'PEN' => 30, 'REQ' => 20],
        ]);

        $this->assertSame(500, $stats->total);
        $this->assertInstanceOf(StatisticsDomainStatisticsByStatus::class, $stats->byStatus);
        $this->assertSame(450, $stats->byStatus->ACT);
        $this->assertSame(30, $stats->byStatus->PEN);
        $this->assertSame(20, $stats->byStatus->REQ);
        $this->assertNull($stats->byStatus->FAI);
        $this->assertSame([
            'total' => 500,
            'by_status' => ['ACT' => 450, 'PEN' => 30, 'REQ' => 20],
        ], $stats->toArray());
    }

    public function testStatisticsLicenseStatisticsConstructor(): void
    {
        $stats = (new StatisticsLicenseStatistics())->fromArray(['total' => 50]);

        $this->assertSame(50, $stats->total);
        $this->assertSame(['total' => 50], $stats->toArray());
    }

    public function testStatisticsSslStatisticsConstructor(): void
    {
        $stats = (new StatisticsSslStatistics())->fromArray([
            'total' => 200,
            'by_status' => ['ACT' => 180, 'REQ' => 20],
        ]);

        $this->assertSame(200, $stats->total);
        $this->assertInstanceOf(StatisticsSslStatisticsByStatus::class, $stats->byStatus);
        $this->assertSame(180, $stats->byStatus->ACT);
        $this->assertSame(20, $stats->byStatus->REQ);
        $this->assertSame(['total' => 200, 'by_status' => ['ACT' => 180, 'REQ' => 20]], $stats->toArray());
    }

    public function testStatisticsSslStatisticsByStatusConstructor(): void
    {
        $data = ['ACT' => 100, 'FAI' => 2, 'NSR' => 1, 'PEN' => 10, 'REJ' => 3, 'REQ' => 4];

        $stats = (new StatisticsSslStatisticsByStatus())->fromArray($data);

        $this->assertSame(100, $stats->ACT);
        $this->assertSame(2, $stats->FAI);
        $this->assertSame(1, $stats->NSR);
        $this->assertSame(10, $stats->PEN);
        $this->assertSame(3, $stats->REJ);
        $this->assertSame(4, $stats->REQ);
        $this->assertSame($data, $stats->toArray());
    }

    public function testEmptyStatisticsCurrencyStatistics(): void
    {
        $stats = new StatisticsCurrencyStatistics();

        $this->assertNull($stats->modified);
        $this->assertSame([], $stats->toArray());
    }
}
