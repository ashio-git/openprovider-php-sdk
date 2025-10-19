<?php

namespace Ashio\Tests;

use Ashio\Exception\ApiException;
use Ashio\Exception\AuthenticationException;
use Ashio\Exception\NotFoundException;
use Ashio\Exception\ValidationException;
use Ashio\OpenProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class OpenProviderTest extends TestCase
{
    public function testConstructorAuthenticatesSuccessfully()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token-123']])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('testuser', 'testpass', $client);

        $this->assertInstanceOf(OpenProvider::class, $op);
    }

    public function testConstructorThrowsAuthenticationException()
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode(['desc' => 'Invalid credentials'])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        new OpenProvider('baduser', 'badpass', $client);
    }

    public function testConstructorThrowsAuthenticationExceptionOnMissingToken()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => []])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Authentication failed');

        new OpenProvider('user', 'pass', $client);
    }

    public function testGetMethodReturnsData()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
            new Response(200, [], json_encode(['data' => ['id' => 123, 'name' => 'test']])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);
        $result = $op->get('test/endpoint');

        $this->assertIsArray($result);
        $this->assertEquals(123, $result['data']['id']);
    }

    public function testPostMethodReturnsData()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
            new Response(201, [], json_encode(['data' => ['id' => 456, 'created' => true]])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);
        $result = $op->post('test/endpoint', ['name' => 'test']);

        $this->assertIsArray($result);
        $this->assertEquals(456, $result['data']['id']);
    }

    public function testPutMethodReturnsData()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
            new Response(200, [], json_encode(['data' => ['id' => 789, 'updated' => true]])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);
        $result = $op->put('test/endpoint/789', ['name' => 'updated']);

        $this->assertIsArray($result);
        $this->assertEquals(789, $result['data']['id']);
    }

    public function testDeleteMethodReturnsData()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
            new Response(200, [], json_encode(['data' => ['success' => true]])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);
        $result = $op->delete('test/endpoint/123');

        $this->assertIsArray($result);
        $this->assertTrue($result['data']['success']);
    }

    public function testThrowsNotFoundExceptionOn404()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
            new Response(404, [], json_encode(['desc' => 'Resource not found'])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Resource not found');

        $op->get('nonexistent/endpoint');
    }

    public function testThrowsValidationExceptionOn422()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
            new Response(422, [], json_encode(['desc' => 'Validation failed'])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');

        $op->post('test/endpoint', ['invalid' => 'data']);
    }

    public function testThrowsApiExceptionOnOtherErrors()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
            new Response(500, [], json_encode(['desc' => 'Internal server error'])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Internal server error');

        $op->get('test/endpoint');
    }

    public function testBillingMethodReturnsInstance()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);
        $billing = $op->billing();

        $this->assertInstanceOf(\Ashio\Accounting\Billing::class, $billing);
    }

    public function testEmailTemplateMethodReturnsInstance()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);
        $emailTemplate = $op->emailTemplate();

        $this->assertInstanceOf(\Ashio\Accounting\EmailTemplate::class, $emailTemplate);
    }

    public function testResellerCustomerMethodReturnsInstance()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);
        $resellerCustomer = $op->resellerCustomer();

        $this->assertInstanceOf(\Ashio\Accounting\ResellerCustomer::class, $resellerCustomer);
    }

    public function testDomainMethodReturnsInstance()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);
        $domain = $op->domain();

        $this->assertInstanceOf(\Ashio\Domain\Domain::class, $domain);
    }

    public function testLicenseMethodReturnsInstance()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);
        $license = $op->license();

        $this->assertInstanceOf(\Ashio\License\License::class, $license);
    }

    public function testSslMethodReturnsInstance()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);
        $ssl = $op->ssl();

        $this->assertInstanceOf(\Ashio\SSL\SSL::class, $ssl);
    }

    public function testHandlerMethodsReturnSameInstance()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['token' => 'test-token']])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $op = new OpenProvider('user', 'pass', $client);

        $billing1 = $op->billing();
        $billing2 = $op->billing();
        $this->assertSame($billing1, $billing2);

        $domain1 = $op->domain();
        $domain2 = $op->domain();
        $this->assertSame($domain1, $domain2);
    }
}
