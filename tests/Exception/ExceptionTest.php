<?php

namespace Ashio\Tests\Exception;

use Ashio\Exception\ApiException;
use Ashio\Exception\AuthenticationException;
use Ashio\Exception\NotFoundException;
use Ashio\Exception\OpenproviderException;
use Ashio\Exception\ParameterException;
use Ashio\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testOpenproviderException()
    {
        $exception = new OpenproviderException('Test message', 100);

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(100, $exception->getCode());
    }

    public function testApiException()
    {
        $exception = new ApiException('API error', 500);

        $this->assertInstanceOf(OpenproviderException::class, $exception);
        $this->assertEquals('API error', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }

    public function testAuthenticationException()
    {
        $exception = new AuthenticationException('Auth failed', 401);

        $this->assertInstanceOf(OpenproviderException::class, $exception);
        $this->assertEquals('Auth failed', $exception->getMessage());
        $this->assertEquals(401, $exception->getCode());
    }

    public function testNotFoundException()
    {
        $exception = new NotFoundException('Not found', 404);

        $this->assertInstanceOf(OpenproviderException::class, $exception);
        $this->assertEquals('Not found', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
    }

    public function testParameterException()
    {
        $exception = new ParameterException('Invalid parameter');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertEquals('Invalid parameter', $exception->getMessage());
    }

    public function testValidationException()
    {
        $exception = new ValidationException('Validation failed', 422);

        $this->assertInstanceOf(OpenproviderException::class, $exception);
        $this->assertEquals('Validation failed', $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
    }

    public function testExceptionWithoutCode()
    {
        $exception = new ApiException('Error without code');

        $this->assertEquals('Error without code', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testExceptionInheritance()
    {
        $this->assertInstanceOf(\Exception::class, new OpenproviderException());
        $this->assertInstanceOf(OpenproviderException::class, new ApiException());
        $this->assertInstanceOf(OpenproviderException::class, new AuthenticationException());
        $this->assertInstanceOf(OpenproviderException::class, new NotFoundException());
        $this->assertInstanceOf(\InvalidArgumentException::class, new ParameterException());
        $this->assertInstanceOf(OpenproviderException::class, new ValidationException());
    }
}
