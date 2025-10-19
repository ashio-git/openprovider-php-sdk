<?php

namespace Ashio\Tests\Model;

use Ashio\Model\StatisticsCurrencyStatistics;
use Ashio\Model\StatisticsDnsStatistics;
use Ashio\Model\StatisticsDomainStatistics;
use Ashio\Model\StatisticsLicenseStatistics;
use Ashio\Model\StatisticsSslStatistics;
use Ashio\Model\StatisticsSslStatisticsByStatus;
use PHPUnit\Framework\TestCase;

class StatisticsCompleteModelsTest extends TestCase
{
    public function testStatisticsCurrencyStatisticsConstructor(): void
    {
        $stats = new StatisticsCurrencyStatistics();
        $stats->currency = 'USD';
        $stats->total = 10000.00;

        $this->assertEquals('USD', $stats->currency);
        $this->assertEquals(10000.00, $stats->total);
    }

    public function testStatisticsDnsStatisticsConstructor(): void
    {
        $stats = new StatisticsDnsStatistics();
        $stats->totalZones = 100;
        $stats->activeZones = 95;

        $this->assertEquals(100, $stats->totalZones);
        $this->assertEquals(95, $stats->activeZones);
    }

    public function testStatisticsDomainStatisticsConstructor(): void
    {
        $stats = new StatisticsDomainStatistics();
        $stats->total = 500;
        $stats->active = 450;

        $this->assertEquals(500, $stats->total);
        $this->assertEquals(450, $stats->active);
    }

    public function testStatisticsLicenseStatisticsConstructor(): void
    {
        $stats = new StatisticsLicenseStatistics();
        $stats->total = 50;
        $stats->active = 45;

        $this->assertEquals(50, $stats->total);
        $this->assertEquals(45, $stats->active);
    }

    public function testStatisticsSslStatisticsConstructor(): void
    {
        $stats = new StatisticsSslStatistics();
        $stats->total = 200;
        $stats->active = 180;

        $this->assertEquals(200, $stats->total);
        $this->assertEquals(180, $stats->active);
    }

    public function testStatisticsSslStatisticsByStatusConstructor(): void
    {
        $stats = new StatisticsSslStatisticsByStatus();
        $stats->active = 100;
        $stats->pending = 10;

        $this->assertEquals(100, $stats->active);
        $this->assertEquals(10, $stats->pending);
    }

    public function testEmptyStatisticsCurrencyStatistics(): void
    {
        $stats = new StatisticsCurrencyStatistics();

        $this->assertNull($stats->currency);
        $this->assertNull($stats->total);
    }
}
