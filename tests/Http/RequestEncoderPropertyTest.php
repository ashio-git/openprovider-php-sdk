<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Http;

use Ashio\OpenProvider\Exception\InvalidArgumentException;
use Ashio\OpenProvider\Http\RequestEncoder;
use Ashio\OpenProvider\Spec\OperationSpec;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Properties of the encoder over generated inputs (fixed seeds, so failures replay).
 */
final class RequestEncoderPropertyTest extends TestCase
{
    private const CASES = 300;
    private const NAME_CHARS = 'abcdefghijklmnopqrstuvwxyz_0123456789';

    /**
     * Every non-null argument appears once per value, in spec order, under its exact (possibly
     * dotted) name; arrays repeat the key in element order; booleans are true/false; null
     * arguments never appear.
     */
    public function testQueryStringsDecodeBackToTheArguments(): void
    {
        $random = new Randomizer(new Mt19937(20261109));
        for ($case = 0; $case < self::CASES; ++$case) {
            $parameters = [];
            $arguments = [];
            $expected = [];
            $names = [];
            $count = $random->getInt(1, 6);
            for ($i = 0; $i < $count; ++$i) {
                $name = self::parameterName($random, $names);
                $type = ['string', 'integer', 'boolean', 'array'][$random->getInt(0, 3)];
                $parameter = ['name' => $name, 'in' => 'query', 'type' => $type];
                if ($type === 'array') {
                    $parameter['items'] = ['type' => 'string'];
                    $parameter['collectionFormat'] = 'multi';
                }
                $parameters[] = $parameter;
                if ($random->getInt(0, 4) === 0) {
                    $arguments[$name] = null;

                    continue;
                }
                $value = self::value($random, $type);
                $arguments[$name] = $value;
                foreach (is_array($value) ? $value : [$value] as $item) {
                    $expected[] = [$name, is_bool($item) ? ($item ? 'true' : 'false') : (string) $item];
                }
            }
            $operation = OperationSpec::fromSwagger('get', '/p', ['operationId' => 'P', 'parameters' => $parameters]);

            $target = (new RequestEncoder())->encode($operation, $arguments)->target;

            self::assertSame($expected, self::decodeQuery($target), "case $case: $target");
            self::assertStringNotContainsString('+', $target, "case $case: spaces must be %20");
        }
    }

    /**
     * Any string survives as exactly one path segment, except the dot segments '.' and '..',
     * which are refused.
     */
    public function testPathValuesStayOneSegmentAndDecodeBack(): void
    {
        $random = new Randomizer(new Mt19937(7));
        $alphabet = "aZ09 /?#%&=+.;,:@!$'()*[]~-_é\u{1F600}";
        $operation = OperationSpec::fromSwagger('get', '/zones/{name}/records', [
            'operationId' => 'P',
            'parameters' => [['name' => 'name', 'in' => 'path', 'required' => true, 'type' => 'string']],
        ]);
        $characters = mb_str_split($alphabet);
        $refused = 0;
        for ($case = 0; $case < self::CASES; ++$case) {
            // The first cases are the dot segments, which the HTTP client would resolve away.
            $value = ['.', '..'][$case] ?? '';
            for ($i = $value === '' ? $random->getInt(1, 12) : 0; $i > 0; --$i) {
                $value .= $characters[$random->getInt(0, count($characters) - 1)];
            }

            try {
                $target = (new RequestEncoder())->encode($operation, ['name' => $value])->target;
            } catch (InvalidArgumentException) {
                self::assertContains($value, ['.', '..'], "case $case was refused");
                ++$refused;

                continue;
            }

            self::assertMatchesRegularExpression('#^/zones/[^/?\#]+/records$#', $target, "case $case");
            self::assertNotContains(explode('/', $target)[2], ['.', '..'], "case $case");
            self::assertSame($value, rawurldecode(explode('/', $target)[2]), "case $case");
        }
        self::assertGreaterThanOrEqual(2, $refused);
    }

    /**
     * The JSON body equals the argument with null map entries removed, and nothing else: 0,
     * false, '' and empty lists survive at every depth, with their types. Lists keep their
     * positions, so a null inside a list stays.
     */
    public function testBodiesKeepEveryNonNullValue(): void
    {
        $random = new Randomizer(new Mt19937(42));
        $operation = OperationSpec::fromSwagger('post', '/p', [
            'operationId' => 'P',
            'parameters' => [['name' => 'body', 'in' => 'body', 'required' => true, 'schema' => []]],
        ]);
        for ($case = 0; $case < self::CASES; ++$case) {
            $body = self::map($random, 3);

            $json = (string) (new RequestEncoder())->encode($operation, ['body' => $body])->body;

            self::assertSame(self::withoutNulls($body), json_decode($json, true), "case $case: $json");
        }
    }

    /**
     * @param list<string> $taken
     */
    private static function parameterName(Randomizer $random, array &$taken): string
    {
        do {
            $segments = [];
            for ($s = $random->getInt(1, 3); $s > 0; --$s) {
                $segment = 'p';
                for ($c = $random->getInt(1, 6); $c > 0; --$c) {
                    $segment .= self::NAME_CHARS[$random->getInt(0, strlen(self::NAME_CHARS) - 1)];
                }
                $segments[] = $segment;
            }
            $name = implode('.', $segments);
        } while (in_array($name, $taken, true));
        $taken[] = $name;

        return $name;
    }

    /**
     * @return int|bool|string|list<string>
     */
    private static function value(Randomizer $random, string $type): int|bool|string|array
    {
        return match ($type) {
            'integer' => [0, -1, 1, PHP_INT_MAX, $random->getInt(-1000, 1000)][$random->getInt(0, 4)],
            'boolean' => $random->getInt(0, 1) === 1,
            'array' => array_map(static fn (): string => self::text($random), range(1, $random->getInt(1, 4))),
            default => self::text($random),
        };
    }

    private static function text(Randomizer $random): string
    {
        $characters = mb_str_split('ab Z9&=?#+%/.é,"\\');
        $text = '';
        for ($i = $random->getInt(0, 8); $i > 0; --$i) {
            $text .= $characters[$random->getInt(0, count($characters) - 1)];
        }

        return $text;
    }

    /**
     * @return array<string, mixed>
     */
    private static function map(Randomizer $random, int $depth): array
    {
        $map = [];
        for ($i = $random->getInt(0, 5); $i > 0; --$i) {
            $map['k' . $random->getInt(0, 99)] = self::anyValue($random, $depth - 1);
        }

        return $map;
    }

    private static function anyValue(Randomizer $random, int $depth): mixed
    {
        $choice = $random->getInt(0, $depth > 0 ? 9 : 6);

        return match ($choice) {
            0 => null,
            1 => 0,
            2 => false,
            3 => '',
            4 => $random->getInt(-5, 5),
            5 => self::text($random),
            6 => true,
            7 => [],
            8 => array_map(static fn (): mixed => self::anyValue($random, 0), range(1, $random->getInt(1, 3))),
            default => self::map($random, $depth),
        };
    }

    private static function withoutNulls(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        $isList = array_is_list($value);
        $out = [];
        foreach ($value as $key => $item) {
            if ($item === null && !$isList) {
                continue;
            }
            $out[$key] = self::withoutNulls($item);
        }

        return $out;
    }

    /**
     * @return list<array{string, string}> [name, value] pairs in order
     */
    private static function decodeQuery(string $target): array
    {
        $query = parse_url('http://h' . $target, PHP_URL_QUERY);
        if (!is_string($query) || $query === '') {
            return [];
        }
        $pairs = [];
        foreach (explode('&', $query) as $pair) {
            [$name, $value] = explode('=', $pair, 2) + [1 => ''];
            $pairs[] = [rawurldecode($name), rawurldecode($value)];
        }

        return $pairs;
    }
}
