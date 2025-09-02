<?php

namespace Ashio\Tests\Accounting;

use PHPUnit\Framework\TestCase;
use Ashio\Accounting\Account;
use Ashio\Model\AccountBalance;
use Ashio\OpenProvider;

class AccountTest extends TestCase
{
    public function testGetBalance(): void
    {
        $mockApi = $this->createMock(OpenProvider::class);
        $mockApi->method('get')->willReturn([
            'data' => [
                'balance' => 100.5,
                'reserved_balance' => 10.0,
            ]
        ]);
        $acc = new Account($mockApi);
        $balance = $acc->getBalance();
        $this->assertInstanceOf(AccountBalance::class, $balance);
        $this->assertSame(100.5, $balance->balance);
        $this->assertSame(10.0, $balance->reserved_balance);
    }
}

