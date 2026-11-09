<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Client;

use Ashio\OpenProvider\Exception\UnexpectedResponseException;
use Ashio\OpenProvider\Response\ApiResponse;
use Ashio\OpenProvider\Serialization\Model;
use Ashio\OpenProvider\Service\Operations;

/**
 * The base of the generated services (src/Service): sends an operation through the client and
 * reads the envelope's `data` into the model the spec declares.
 *
 * Reading is tolerant like the models are: absent or null `data` reads as an empty object (or an
 * empty list), and unknown keys stay in the model's `$extra`. Only data of another JSON type than
 * the spec declares cannot be read; that throws UnexpectedResponseException, whose message says
 * that the API reported success, so the caller knows the call itself was not refused.
 */
abstract class AbstractService
{
    /**
     * Final: the facade and the audit build every service from the client alone.
     */
    final public function __construct(protected readonly ApiClient $client)
    {
    }

    /**
     * @param array<string, mixed> $arguments keyed by the spec's parameter names
     *
     * @return ApiResponse<mixed>
     */
    protected function call(string $operationId, array $arguments): ApiResponse
    {
        return $this->client->call(Operations::get($operationId), $arguments);
    }

    /**
     * @template T of Model
     *
     * @param ApiResponse<mixed> $response
     * @param class-string<T>    $class
     *
     * @return ApiResponse<T>
     */
    protected static function object(ApiResponse $response, string $class): ApiResponse
    {
        $data = $response->data ?? [];
        if (!is_array($data)) {
            throw self::unreadable($response, $class, 'an object');
        }

        return $response->withData($class::fromArray($data));
    }

    /**
     * @template T of Model
     *
     * @param ApiResponse<mixed> $response
     * @param class-string<T>    $class
     *
     * @return ApiResponse<list<T>>
     */
    protected static function objects(ApiResponse $response, string $class): ApiResponse
    {
        $data = $response->data ?? [];
        if (!is_array($data) || !array_is_list($data)) {
            throw self::unreadable($response, $class, 'a list of objects');
        }
        $items = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                throw self::unreadable($response, $class, 'a list of objects');
            }
            $items[] = $class::fromArray($item);
        }

        return $response->withData($items);
    }

    /**
     * @param ApiResponse<mixed> $response
     */
    private static function unreadable(ApiResponse $response, string $class, string $expected): UnexpectedResponseException
    {
        return new UnexpectedResponseException(sprintf(
            'The API reported success (code 0), but its data is %s where the spec declares %s of %s',
            get_debug_type($response->data),
            $expected,
            $class,
        ), $response->httpStatus);
    }
}
