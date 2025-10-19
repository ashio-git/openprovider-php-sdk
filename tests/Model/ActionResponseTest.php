<?php

namespace Ashio\Tests\Model;

use Ashio\Model\ActionResponse;
use PHPUnit\Framework\TestCase;

class ActionResponseTest extends TestCase
{
    public function testFromArrayWithSuccess()
    {
        $data = ['success' => true];

        $response = (new ActionResponse())->fromArray($data);

        $this->assertTrue($response->success);
    }

    public function testFromArrayWithFailure()
    {
        $data = ['success' => false];

        $response = (new ActionResponse())->fromArray($data);

        $this->assertFalse($response->success);
    }

    public function testFromArrayWithMissingSuccess()
    {
        $data = [];

        $response = (new ActionResponse())->fromArray($data);

        $this->assertNull($response->success);
    }

    public function testFromArrayChaining()
    {
        $response = (new ActionResponse())->fromArray(['success' => true]);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertTrue($response->success);
    }

    public function testSuccessProperty()
    {
        $response = new ActionResponse();
        $response->success = true;

        $this->assertTrue($response->success);
    }

    public function testFailureProperty()
    {
        $response = new ActionResponse();
        $response->success = false;

        $this->assertFalse($response->success);
    }

    public function testEmptyActionResponse()
    {
        $response = new ActionResponse();

        $this->assertNull($response->success);
    }

    public function testBooleanCoercion()
    {
        $response = new ActionResponse();
        $response->success = true;

        if ($response->success) {
            $this->assertTrue(true);
        } else {
            $this->fail('Success should be true');
        }
    }
}
