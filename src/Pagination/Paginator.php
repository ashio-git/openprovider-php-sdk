<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Pagination;

use Ashio\OpenProvider\Client\AbstractService;
use Ashio\OpenProvider\Exception\InvalidArgumentException;
use Ashio\OpenProvider\Exception\UnexpectedResponseException;
use Ashio\OpenProvider\Response\ApiResponse;
use Ashio\OpenProvider\Serialization\Model;
use Ashio\OpenProvider\Service\Operations;
use Ashio\OpenProvider\Spec\Operation;
use Ashio\OpenProvider\Spec\OperationSpec;

/**
 * Walks a limit/offset listing page by page.
 *
 *     $domains = Paginator::all($openProvider->domain()->listDomains(...), ['status' => 'ACT'], of: DomainGetDomainResponseData::class);
 *     foreach ($domains as $domain) {
 *         // one domain at a time; the next page is fetched when this one runs out
 *     }
 *
 * A page is as large as the endpoint allows: the maximum spec/behavior.json documents (1000 for
 * domains and SSL orders, 500 for DNS zones and records), or the API's default of 100 where none
 * is documented. Fewer, larger pages keep a long listing clear of the per-call rate limits.
 *
 * The walk ends at an empty page, when `total` is reached, or, when the API sends no total, at a
 * page shorter than the limit. A short page with more to come by `total` is followed, so an
 * endpoint that caps its pages below the limit cannot cut a listing short.
 */
final class Paginator
{
    /** The page size the API uses when a call sets no limit (spec/behavior.json `paging.defaultLimit`). */
    public const DEFAULT_LIMIT = 100;

    /**
     * Every item of a paged listing. $list is a service's list method as a first-class callable;
     * $arguments are its other arguments by name, sent with every page. $pageSize lowers the
     * page size, and never raises it above the endpoint's maximum. $of names the items' class, so
     * static analysis and IDEs know what each item is; every item is checked against it, and a
     * page of another class throws before any of its items is yielded. Other misuse is refused
     * here, before anything is sent.
     *
     * @template T of Model
     *
     * @param \Closure             $list
     * @param array<string, mixed> $arguments
     * @param class-string<T>|null $of
     *
     * @return \Generator<int, ($of is null ? Model : T), mixed, void>
     *
     * @throws InvalidArgumentException when $list is not a paged listing, an argument does not fit
     *                                  it, or an item is not an $of
     */
    public static function all(\Closure $list, array $arguments = [], ?int $pageSize = null, ?string $of = null): \Generator
    {
        [$operation, $parameters] = self::listing($list);
        foreach (array_keys($arguments) as $name) {
            if ($name === 'limit' || $name === 'offset') {
                throw new InvalidArgumentException('Paginator::all() sets limit and offset itself');
            }
            if (!in_array($name, $parameters, true)) {
                throw new InvalidArgumentException(sprintf('%s() has no parameter $%s; it takes $%s', (new \ReflectionFunction($list))->getName(), $name, implode(', $', $parameters)));
            }
        }
        if ($pageSize !== null && $pageSize < 1) {
            throw new InvalidArgumentException('pageSize must be at least 1');
        }
        if ($of !== null && !is_subclass_of($of, Model::class)) {
            throw new InvalidArgumentException(sprintf('of must name a model class; %s is not one', $of));
        }
        $limit = $pageSize ?? $operation->maxLimit ?? self::DEFAULT_LIMIT;
        if ($operation->maxLimit !== null) {
            $limit = min($limit, $operation->maxLimit);
        }

        return self::walk(
            static fn (int $limit, int $offset): Page => self::page($operation->operationId, $list(...$arguments, limit: $limit, offset: $offset), $of),
            $limit,
        );
    }

    /**
     * The low-level walk: $fetch receives (limit, offset) and returns one page.
     *
     * @template T
     *
     * @param \Closure(int, int): Page<T> $fetch receives (limit, offset)
     *
     * @return \Generator<int, T, mixed, void>
     */
    public static function iterate(\Closure $fetch, int $pageSize = self::DEFAULT_LIMIT, ?int $maxLimit = null): \Generator
    {
        if ($pageSize < 1 || ($maxLimit !== null && $maxLimit < 1)) {
            throw new InvalidArgumentException('pageSize and maxLimit must be at least 1');
        }

        return self::walk($fetch, $maxLimit === null ? $pageSize : min($pageSize, $maxLimit));
    }

    /**
     * The operation behind a service's list method, and the names of the method's parameters.
     *
     * @return array{OperationSpec, list<string>}
     */
    private static function listing(\Closure $list): array
    {
        $function = new \ReflectionFunction($list);
        $class = $function->getClosureScopeClass();
        $method = $class !== null && $class->isSubclassOf(AbstractService::class) && $class->hasMethod($function->getName())
            ? $class->getMethod($function->getName())
            : null;
        $attribute = $method?->getAttributes(Operation::class)[0] ?? null;
        if ($method === null || $attribute === null || !$function->getClosureThis() instanceof AbstractService) {
            throw new InvalidArgumentException('Paginator::all() takes a service\'s list method as a first-class callable, e.g. $openProvider->domain()->listDomains(...)');
        }
        $operation = Operations::get($attribute->newInstance()->operationId);
        if (!$operation->paged) {
            throw new InvalidArgumentException(sprintf('%s is not a paged listing: it does not take limit and offset and return results', $operation->operationId));
        }

        return [$operation, array_map(static fn (\ReflectionParameter $p): string => $p->getName(), $method->getParameters())];
    }

    /**
     * One page of a listing's response: its items, checked against $of, and `total`.
     *
     * @template T of Model
     *
     * @param class-string<T>|null $of
     *
     * @return Page<Model>
     */
    private static function page(string $operationId, mixed $response, ?string $of): Page
    {
        if (!$response instanceof ApiResponse || !$response->data instanceof Listing) {
            throw new \LogicException(sprintf('%s did not return an ApiResponse holding a Listing', $operationId));
        }
        $listing = $response->data;
        $fields = get_object_vars($listing);
        if (isset($fields['extra']) && is_array($fields['extra']) && array_key_exists('results', $fields['extra'])) {
            // The model could not read `results` into its typed list: stopping here would look like
            // the end of the listing.
            throw new UnexpectedResponseException(sprintf('%s returned "results" that is not a list of models', $operationId), $response->httpStatus);
        }
        $items = $listing->items();
        foreach ($items as $item) {
            if ($of !== null && !$item instanceof $of) {
                throw new InvalidArgumentException(sprintf('%s lists %s, not %s', $operationId, $item::class, $of));
            }
        }

        return new Page($items, $listing->total());
    }

    /**
     * @template T
     *
     * @param \Closure(int, int): Page<T> $fetch
     *
     * @return \Generator<int, T, mixed, void>
     */
    private static function walk(\Closure $fetch, int $limit): \Generator
    {
        $offset = 0;
        while (true) {
            $page = $fetch($limit, $offset);
            foreach ($page->items as $item) {
                yield $item;
            }
            $offset += count($page->items);
            $last = $page->total === null ? count($page->items) < $limit : $offset >= $page->total;
            if ($page->items === [] || $last) {
                return;
            }
        }
    }
}
