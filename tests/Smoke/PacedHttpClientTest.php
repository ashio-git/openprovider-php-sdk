<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Smoke;

use Ashio\OpenProvider\Smoke\Support\PacedHttpClient;
use Ashio\OpenProvider\Tests\Support\FakeHttpClient;
use Ashio\OpenProvider\Tests\Support\RecordingSleeper;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

/**
 * The smoke suite's pacing, offline: requests are spaced by the interval measured from the end
 * of the previous one, and the budget is a hard stop.
 */
final class PacedHttpClientTest extends TestCase
{
    private float $time = 100.0;

    public function testTheFirstRequestGoesAtOnceAndTheNextWaitForTheRestOfTheInterval(): void
    {
        $sleeper = new RecordingSleeper();
        $paced = $this->paced((new FakeHttpClient())->reply(['code' => 0])->reply(['code' => 0])->reply(['code' => 0]), $sleeper, 1000, 10);

        $paced->sendRequest(new Request('GET', '/a'));
        $this->time += 0.25;
        $paced->sendRequest(new Request('GET', '/b'));
        $this->time += 2.0;
        $paced->sendRequest(new Request('GET', '/c'));

        self::assertSame([750], $sleeper->sleeps);
        self::assertSame(3, $paced->sent());
    }

    public function testTheIntervalCountsFromTheEndOfAFailedRequestToo(): void
    {
        $sleeper = new RecordingSleeper();
        $paced = $this->paced((new FakeHttpClient())->fail(new \RuntimeException('down'))->reply(['code' => 0]), $sleeper, 500, 10);

        try {
            $paced->sendRequest(new Request('GET', '/a'));
            self::fail('no exception');
        } catch (\RuntimeException $e) {
            self::assertSame('down', $e->getMessage());
        }
        $paced->sendRequest(new Request('GET', '/b'));

        self::assertSame([500], $sleeper->sleeps);
    }

    public function testTheBudgetIsAHardStop(): void
    {
        $http = (new FakeHttpClient())->reply(['code' => 0])->reply(['code' => 0]);
        $paced = $this->paced($http, new RecordingSleeper(), 0, 1);
        $paced->sendRequest(new Request('GET', '/a'));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The smoke suite may send 1 requests per run; GET /b would be one more');

        try {
            $paced->sendRequest(new Request('GET', '/b'));
        } finally {
            self::assertSame(1, $http->pending(), 'the refused request never reached the transport');
        }
    }

    public function testANegativeIntervalOrAnEmptyBudgetIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->paced(new FakeHttpClient(), new RecordingSleeper(), 1000, 0);
    }

    private function paced(FakeHttpClient $http, RecordingSleeper $sleeper, int $intervalMs, int $budget): PacedHttpClient
    {
        return new PacedHttpClient($http, $sleeper, fn (): float => $this->time, $intervalMs, $budget);
    }
}
