<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Exception;

use Ashio\OpenProvider\Exception\ApiException;
use Ashio\OpenProvider\Exception\AuthenticationException;
use Ashio\OpenProvider\Exception\ErrorCatalog;
use Ashio\OpenProvider\Exception\ErrorCategory;
use Ashio\OpenProvider\Exception\ExceptionFactory;
use Ashio\OpenProvider\Exception\MaintenanceException;
use Ashio\OpenProvider\Exception\NotFoundException;
use Ashio\OpenProvider\Exception\OpenProviderException;
use Ashio\OpenProvider\Exception\RateLimitedException;
use Ashio\OpenProvider\Exception\ValidationException;
use Ashio\OpenProvider\Response\ApiResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExceptionFactoryTest extends TestCase
{
    /**
     * @return iterable<string, array{int, int, class-string<ApiException>}>
     */
    public static function codes(): iterable
    {
        yield 'expired token, production status' => [196, 500, AuthenticationException::class];
        yield 'expired token, sandbox status' => [196, 401, AuthenticationException::class];
        yield 'ip not allowed' => [10005, 500, AuthenticationException::class];
        yield '2fa required' => [10006, 500, AuthenticationException::class];
        yield 'calls limit' => [50, 500, RateLimitedException::class];
        yield 'excessive use' => [4007, 500, RateLimitedException::class];
        yield 'api maintenance' => [4005, 500, MaintenanceException::class];
        yield 'registry unreachable' => [10, 500, MaintenanceException::class];
        yield 'zone not found' => [872, 500, NotFoundException::class];
        yield 'invalid request' => [80, 500, ValidationException::class];
        yield 'invalid zipcode' => [116, 500, ValidationException::class];
        yield 'insufficient credit' => [310, 500, ApiException::class];
        yield 'unknown code' => [987654, 200, ApiException::class];
    }

    /**
     * @param class-string<ApiException> $class
     */
    #[DataProvider('codes')]
    public function testClassifiesByTheApiCodeNotTheHttpStatus(int $code, int $status, string $class): void
    {
        $exception = ExceptionFactory::fromResponse(
            ApiResponse::fromHttp($status, (string) json_encode(['code' => $code, 'desc' => 'from server', 'data' => ['field' => 'x']])),
        );

        self::assertSame($class, $exception::class);
        self::assertInstanceOf(OpenProviderException::class, $exception);
        self::assertSame($code, $exception->apiCode);
        self::assertSame($code, $exception->getCode());
        self::assertSame($status, $exception->httpStatus);
        self::assertSame(['field' => 'x'], $exception->data);
        self::assertSame("Openprovider API error $code: from server", $exception->getMessage());
    }

    public function testTheMaintenanceFlagMakesAnUnclassifiedErrorAMaintenanceError(): void
    {
        $exception = ExceptionFactory::fromResponse(ApiResponse::fromHttp(500, '{"code":310,"desc":"x","maintenance":true}'));

        self::assertInstanceOf(MaintenanceException::class, $exception);
        self::assertTrue($exception->maintenance);
    }

    public function testAnEmptyDescriptionFallsBackToTheCatalog(): void
    {
        $exception = ExceptionFactory::fromResponse(ApiResponse::fromHttp(500, '{"code":196}'));

        self::assertSame('Openprovider API error 196: Authentication/Authorization Failed', $exception->getMessage());
        self::assertSame(
            'Authorization failed due to incorrect credentials/invalid token, make sure correct data is used.',
            $exception->resolution(),
        );
    }

    public function testWarningsTravelWithTheException(): void
    {
        $exception = ExceptionFactory::fromResponse(
            ApiResponse::fromHttp(500, '{"code":80,"desc":"x","warnings":[{"code":1,"desc":"w"}]}'),
        );

        self::assertCount(1, $exception->warnings);
        self::assertSame('w', $exception->warnings[0]->desc);
    }

    public function testASuccessfulResponseIsNotAnError(): void
    {
        $this->expectException(\LogicException::class);

        ExceptionFactory::fromResponse(ApiResponse::fromHttp(200, '{"code":0}'));
    }

    public function testTheCatalogKnowsEveryCodeOfTheKnowledgeBase(): void
    {
        self::assertSame(608, ErrorCatalog::count());
        self::assertSame('Two factor authentication required', ErrorCatalog::describe(10006));
        self::assertNull(ErrorCatalog::resolution(10006));
        self::assertNull(ErrorCatalog::describe(987654));
        self::assertSame(ErrorCategory::Other, ErrorCatalog::category(987654));
        self::assertSame(ErrorCategory::Authentication, ErrorCatalog::category(196));
    }
}
