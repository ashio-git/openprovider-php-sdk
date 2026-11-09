<?php

namespace Ashio\Tests;

use Ashio\Credentials;
use PHPUnit\Framework\TestCase;

class CredentialsTest extends TestCase
{
    public function testConstructorSetsToken()
    {
        $token = 'test-token-123';
        $credentials = new Credentials($token);

        $this->assertInstanceOf(Credentials::class, $credentials);
    }

    public function testGetUrlReturnsCorrectUrl()
    {
        $credentials = new Credentials('test-token');
        $url = $credentials->getUrl();

        $this->assertEquals('https://api.openprovider.eu/v1beta/', $url);
    }

    public function testGetTokenReturnsToken()
    {
        $token = 'my-secret-token';
        $credentials = new Credentials($token);

        $this->assertEquals($token, $credentials->getToken());
    }
}
