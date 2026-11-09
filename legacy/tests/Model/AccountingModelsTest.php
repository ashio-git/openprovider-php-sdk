<?php

namespace Ashio\Tests\Model;

use Ashio\Model\AccountBalance;
use Ashio\Model\AuthCode;
use Ashio\Model\AuthInfo;
use PHPUnit\Framework\TestCase;

class AccountingModelsTest extends TestCase
{
    public function testAccountBalanceFromArray(): void
    {
        $data = [
            'balance' => 1000.50,
            'reserved_balance' => 250.00,
        ];

        $balance = (new AccountBalance())->fromArray($data);

        $this->assertEquals(1000.50, $balance->balance);
        $this->assertEquals(250.00, $balance->reserved_balance);
    }

    public function testAuthCodeFromArray(): void
    {
        $data = [
            'auth_code' => 'ABC123XYZ',
            'success' => true,
            'type' => 'epp',
        ];

        $authCode = (new AuthCode())->fromArray($data);

        $this->assertEquals('ABC123XYZ', $authCode->auth_code);
        $this->assertTrue($authCode->success);
        $this->assertEquals('epp', $authCode->type);
    }

    public function testAuthInfoFromArray(): void
    {
        $data = [
            'pw' => 'secret123',
        ];

        $authInfo = (new AuthInfo())->fromArray($data);

        $this->assertEquals('secret123', $authInfo->pw);
    }

    public function testEmptyAccountBalance(): void
    {
        $balance = new AccountBalance();

        $this->assertNull($balance->balance);
        $this->assertNull($balance->reserved_balance);
    }

    public function testEmptyAuthCode(): void
    {
        $authCode = new AuthCode();

        $this->assertNull($authCode->auth_code);
        $this->assertNull($authCode->success);
    }

    public function testEmptyAuthInfo(): void
    {
        $authInfo = new AuthInfo();

        $this->assertNull($authInfo->pw);
    }

    public function testAccountBalanceWithNegativeValue(): void
    {
        $data = [
            'balance' => -250.75,
            'reserved_balance' => 0.0,
        ];

        $balance = (new AccountBalance())->fromArray($data);

        $this->assertEquals(-250.75, $balance->balance);
        $this->assertEquals(0.0, $balance->reserved_balance);
    }

    public function testAuthCodeWithAuthInfo(): void
    {
        $data = [
            'auth_code' => 'TEST123',
            'auth_info' => ['pw' => 'password123'],
        ];

        $authCode = (new AuthCode())->fromArray($data);

        $this->assertEquals('TEST123', $authCode->auth_code);
        $this->assertInstanceOf(AuthInfo::class, $authCode->auth_info);
        $this->assertEquals('password123', $authCode->auth_info->pw);
    }
}
