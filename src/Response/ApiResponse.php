<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Response;

use Ashio\OpenProvider\Exception\UnexpectedResponseException;

/**
 * The API envelope `{code, desc, data, warnings, maintenance}`. Success is `code === 0`,
 * whatever the HTTP status says.
 *
 * `data` is the decoded JSON as received (`ApiResponse<mixed>`) until a service reads it into
 * its model with withData(); the services return `ApiResponse<SomeModel>`.
 *
 * @template-covariant T
 */
final class ApiResponse
{
    /**
     * @param T             $data
     * @param list<Warning> $warnings
     */
    public function __construct(
        public readonly int $code,
        public readonly string $desc,
        public readonly mixed $data,
        public readonly array $warnings,
        public readonly bool $maintenance,
        public readonly int $httpStatus,
    ) {
    }

    /**
     * The body is a #[\SensitiveParameter] and the JsonException is not chained: a login answer
     * holds the token, and a trace (with zend.exception_ignore_args off) would show it.
     *
     * @return self<mixed>
     *
     * @throws UnexpectedResponseException when the body is not a JSON envelope with an integer code
     */
    public static function fromHttp(int $status, #[\SensitiveParameter] string $body): self
    {
        try {
            $envelope = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new UnexpectedResponseException(
                sprintf('HTTP %d response is not JSON (%d bytes): %s', $status, strlen($body), $e->getMessage()),
                $status,
            );
        }
        if (!is_array($envelope) || array_is_list($envelope) || !isset($envelope['code']) || !is_int($envelope['code'])) {
            throw new UnexpectedResponseException(
                sprintf('HTTP %d response is not an API envelope: it has no integer "code"', $status),
                $status,
            );
        }

        return new self(
            $envelope['code'],
            isset($envelope['desc']) && is_string($envelope['desc']) ? $envelope['desc'] : '',
            $envelope['data'] ?? null,
            self::warnings($envelope['warnings'] ?? null),
            ($envelope['maintenance'] ?? false) === true,
            $status,
        );
    }

    public function isSuccess(): bool
    {
        return $this->code === 0;
    }

    /**
     * The same envelope with `data` replaced, typically by the model read from it.
     *
     * @template U
     *
     * @param U $data
     *
     * @return self<U>
     */
    public function withData(mixed $data): self
    {
        return new self($this->code, $this->desc, $data, $this->warnings, $this->maintenance, $this->httpStatus);
    }

    /**
     * Warnings never make a response unreadable: entries that are not objects are skipped.
     *
     * @return list<Warning>
     */
    private static function warnings(mixed $warnings): array
    {
        if (!is_array($warnings)) {
            return [];
        }
        $out = [];
        foreach ($warnings as $warning) {
            if (!is_array($warning) || array_is_list($warning)) {
                continue;
            }
            $out[] = new Warning(
                self::warningCode($warning['code'] ?? null),
                isset($warning['desc']) && is_string($warning['desc']) ? $warning['desc'] : '',
                $warning['data'] ?? null,
            );
        }

        return $out;
    }

    /**
     * An integer code, or one sent as a string of digits; anything else is 0.
     */
    private static function warningCode(mixed $code): int
    {
        return is_int($code) ? $code : (is_string($code) && ctype_digit($code) ? (int) $code : 0);
    }
}
