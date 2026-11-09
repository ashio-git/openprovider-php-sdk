<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Support;

use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\Service\Operations;
use Ashio\OpenProvider\Tools\Conformance\SpecSamples;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A PSR-18 client that answers like the API would, from the spec alone: the login returns a
 * token, and any other request gets a success envelope whose `data` is the spec's example for the
 * operation it matches (method and path, literal paths before templated ones). A request that
 * matches no operation is an error, so an example calling a route the spec lacks fails.
 */
final class SpecTransport implements ClientInterface
{
    /** @var list<RequestInterface> */
    private array $requests = [];

    #[\Override]
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        $path = (string) preg_replace('#^' . preg_quote(ClientConfig::DEFAULT_BASE_PATH, '#') . '#', '', $request->getUri()->getPath());
        if ($request->getMethod() === 'POST' && $path === '/auth/login') {
            return self::json(['code' => 0, 'desc' => '', 'data' => ['token' => 'readme-token', 'reseller_id' => 1]]);
        }
        $operationId = self::operationId($request->getMethod(), $path)
            ?? throw new \LogicException(sprintf('No operation of the spec is %s %s', $request->getMethod(), $path));
        $envelope = SpecOperations::refName(ArrayPath::entries(SpecOperations::all()[$operationId]['operation'], 'responses', '200', 'schema'));

        return self::json(['code' => 0, 'desc' => '', 'data' => (new SpecSamples(SpecOperations::definitions()))->data($envelope)]);
    }

    /**
     * The requests sent so far, the login excluded.
     *
     * @return list<RequestInterface>
     */
    public function calls(): array
    {
        return array_values(array_filter($this->requests, static fn (RequestInterface $r): bool => !str_ends_with($r->getUri()->getPath(), '/auth/login')));
    }

    public function sent(): int
    {
        return count($this->requests);
    }

    private static function operationId(string $method, string $path): ?string
    {
        $candidates = [];
        foreach (Operations::IDS as $id) {
            $operation = Operations::get($id);
            $pattern = '#^' . preg_replace('#\\\{[^}]+\\\}#', '[^/]+', preg_quote($operation->path, '#')) . '$#';
            if ($operation->method === $method && preg_match($pattern, $path) === 1) {
                $candidates[$id] = substr_count($operation->path, '{');
            }
        }
        asort($candidates);

        return array_key_first($candidates);
    }

    /**
     * @param array<string, mixed> $envelope
     */
    private static function json(array $envelope): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($envelope, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
    }
}
