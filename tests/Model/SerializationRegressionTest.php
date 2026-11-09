<?php

declare(strict_types=1);

namespace Ashio\Tests\Model;

use Ashio\Model\SettingsPaymentMethodLimit;
use Ashio\Model\SslOrder;
use Ashio\Model\SslOrderDomainValidationStatuses;
use Ashio\Model\SslOrderOptions;
use Ashio\Model\StatisticsCurrencyStatistics;
use Ashio\Model\StatisticsCustomerStatistics;
use Ashio\Model\StatisticsDnsStatistics;
use Ashio\Model\StatisticsDomainStatisticsByStatus;
use Ashio\Model\StatisticsLicenseStatistics;
use Ashio\Model\StatisticsSpamExpertsStatistics;
use Ashio\Model\StatisticsSslStatisticsByStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Regressions for model (de)serialization bugs found against the Openprovider swagger.
 */
class SerializationRegressionTest extends TestCase
{
    public function testSslOrderMapsNestedObjectsFromARealPayload(): void
    {
        $order = (new SslOrder())->fromArray([
            'id' => 7,
            'common_name' => 'example.com',
            'options' => ['dummy' => true],
            'domain_validation_statuses' => ['example.com' => 'valid'],
        ]);

        $this->assertSame(7, $order->id);
        $this->assertSame('example.com', $order->common_name);
        $this->assertInstanceOf(SslOrderOptions::class, $order->options);
        $this->assertInstanceOf(SslOrderDomainValidationStatuses::class, $order->domain_validation_statuses);
    }

    /**
     * @return iterable<string, array{class-string, array<string, int|float|string>}>
     */
    public static function zeroValuedPayloads(): iterable
    {
        yield 'dns total' => [StatisticsDnsStatistics::class, ['total' => 0]];
        yield 'license total' => [StatisticsLicenseStatistics::class, ['total' => 0]];
        yield 'customer total' => [StatisticsCustomerStatistics::class, ['modified' => '2026-01-01 00:00:00', 'total' => 0]];
        yield 'currency modified' => [StatisticsCurrencyStatistics::class, ['modified' => '0']];
        yield 'domain status counts' => [StatisticsDomainStatisticsByStatus::class, ['ACT' => 3, 'FAI' => 0]];
        yield 'ssl status counts' => [StatisticsSslStatisticsByStatus::class, ['ACT' => 0, 'PEN' => 2]];
        yield 'payment limit' => [SettingsPaymentMethodLimit::class, ['EUR' => 0.0, 'USD' => 50.0]];
    }

    /**
     * @param class-string $class
     * @param array<string, int|float|string> $payload
     */
    #[DataProvider('zeroValuedPayloads')]
    public function testToArrayKeepsZeroValues(string $class, array $payload): void
    {
        $model = (new $class())->fromArray($payload);

        $this->assertSame($payload, $model->toArray());
    }

    public function testSpamExpertsStatisticsRoundTripsWithApiKeys(): void
    {
        $payload = [
            'activated_at' => '2026-01-01 00:00:00',
            'created_at' => '2025-12-31 00:00:00',
            'exists' => false,
            'expired_at' => '2027-01-01 00:00:00',
            'incoming_count' => 0,
            'incoming_limit' => 100,
            'outgoing_count' => 0,
            'outgoing_limit' => 50,
            'status' => 'ACT',
            'with_outgoing_filter' => false,
        ];

        $this->assertSame($payload, (new StatisticsSpamExpertsStatistics())->fromArray($payload)->toArray());
    }
}
