<?php

namespace Vexura\Tests\License;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Vexura\Exception\NotFoundException;
use Vexura\Exception\ValidationException;
use Vexura\Model\License as LicenseModel;
use Vexura\Model\LicenseItem as LicenseItemModel;
use Vexura\Model\LicenseKey as LicenseKeyModel;
use Vexura\Model\PaginatedResponse;
use Vexura\OpenProvider;
use Vexura\License\License;

class LicenseAdvancedTest extends TestCase
{
    private function makeClient(array $responses): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack, 'http_errors' => false]);
    }

    private function loginOk(): Response
    {
        return new Response(200, [], json_encode(['data' => ['token' => 'test']])) ;
    }

    public function testGetPleskKey(): void
    {
        $payload = ['data' => ['key' => 'S-PLSK-KEY']];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($payload)),
        ]);
        $api = new OpenProvider('u','p',$client);
        $lic = new License($api);
        $key = $lic->getPleskKey(1);
        $this->assertInstanceOf(LicenseKeyModel::class, $key);
        $this->assertSame('S-PLSK-KEY', $key->key);
    }

    public function testCreatePleskLicenseAndListItems(): void
    {
        $create = ['data' => ['key_id' => 100, 'product' => 'plesk', 'status' => 'active']];
        $listItems = ['data' => ['total' => 1, 'results' => [['item' => 'FT-ITEM', 'product' => 'plesk', 'title' => 'Item']]]];
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(200, [], json_encode($create)),
            new Response(200, [], json_encode($listItems)),
        ]);
        $api = new OpenProvider('u','p',$client);
        $lic = new License($api);
        $created = $lic->createPleskLicense(new \Vexura\Model\CreatePleskLicenseRequest(items: ['ITEM1'], period: 1, title: 'My License'));
        $this->assertInstanceOf(LicenseModel::class, $created);
        $this->assertSame(100, $created->key_id);

        $items = $lic->listItems();
        $this->assertInstanceOf(PaginatedResponse::class, $items);
        $this->assertInstanceOf(LicenseItemModel::class, $items->results[0]);
    }

    public function testUpdatePleskLicenseNotFound(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(404, [], json_encode(['desc' => 'Not found'])),
        ]);
        $api = new OpenProvider('u','p',$client);
        $lic = new License($api);
        $this->expectException(NotFoundException::class);
        $lic->updatePleskLicense(999, new \Vexura\Model\UpdatePleskLicenseRequest());
    }

    public function testCreateVirtuozzoValidationError(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(422, [], json_encode(['desc' => 'Invalid'])),
        ]);
        $api = new OpenProvider('u','p',$client);
        $lic = new License($api);
        $this->expectException(ValidationException::class);
        $lic->createVirtuozzoLicense(new \Vexura\Model\CreateVirtuozzoLicenseRequest(items: ['ITEM']));
    }

    public function testGetPleskLicenseUnauthorized401(): void
    {
        $client = $this->makeClient([
            $this->loginOk(),
            new Response(401, [], json_encode(['desc' => 'Unauthorized'])),
        ]);
        $api = new OpenProvider('u','p',$client);
        $lic = new License($api);
        $this->expectException(\Vexura\Exception\AuthenticationException::class);
        $lic->getPleskLicense(1);
    }
}
