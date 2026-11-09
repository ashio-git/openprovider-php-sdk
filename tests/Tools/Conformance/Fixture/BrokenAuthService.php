<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Conformance\Fixture;

use Ashio\OpenProvider\Client\AbstractService;
use Ashio\OpenProvider\Model\Auth\AuthLoginRequest;
use Ashio\OpenProvider\Model\Auth\AuthLoginResponseData;
use Ashio\OpenProvider\Model\EasyDmarc\OrderDeleteEasyDmarcResponseData;
use Ashio\OpenProvider\Model\Portal\SignupCreateResellerRequest;
use Ashio\OpenProvider\Response\ApiResponse;
use Ashio\OpenProvider\Spec\Operation;
use Ashio\OpenProvider\Spec\OperationSpec;
use Ashio\OpenProvider\Spec\ParameterLocation;
use Ashio\OpenProvider\Spec\ParameterSpec;

/**
 * Planted defects (see BrokenDnsService).
 */
final class BrokenAuthService extends AbstractService
{
    /**
     * Sends a token with the login, which the API serves without one.
     *
     * @return ApiResponse<AuthLoginResponseData>
     */
    #[Operation('POST', '/auth/login', 'Login')]
    public function login(AuthLoginRequest $body): ApiResponse
    {
        $operation = new OperationSpec('POST', '/auth/login', 'Login', [new ParameterSpec('body', ParameterLocation::Body, true)]);

        return self::object($this->client->call($operation, ['body' => $body]), AuthLoginResponseData::class);
    }

    /**
     * Not marked experimental, and read into another operation's model.
     *
     * @return ApiResponse<AuthLoginResponseData>
     */
    #[Operation('POST', '/resellers/signup', 'CreateResellerPublic')]
    public function createResellerPublic(SignupCreateResellerRequest $body): ApiResponse
    {
        return self::object($this->call('CreateResellerPublic', ['body' => $body]), AuthLoginResponseData::class);
    }

    /**
     * An EasyDmarc operation in the Auth service: one service per tag group.
     *
     * @return ApiResponse<OrderDeleteEasyDmarcResponseData>
     */
    #[Operation('DELETE', '/easydmarcs/{id}', 'DeleteEasyDmarc')]
    public function deleteEasyDmarc(int $id): ApiResponse
    {
        return self::object($this->call('DeleteEasyDmarc', ['id' => $id]), OrderDeleteEasyDmarcResponseData::class);
    }
}
