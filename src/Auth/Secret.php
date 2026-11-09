<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Auth;

/**
 * A value that must not leak. The value is not a property of the object: it sits in a static
 * WeakMap keyed by the object, so var_export, print_r, var_dump, json_encode and (array)
 * casts have nothing to show. (A closure would not do: print_r and var_dump of a closure list
 * its captured variables.) Serialization and cloning are refused; the map entry goes away
 * with the object.
 */
final class Secret implements \Stringable
{
    private const MASK = '********';

    /** @var \WeakMap<object, mixed>|null */
    private static ?\WeakMap $values = null;

    public function __construct(#[\SensitiveParameter] string $value)
    {
        self::$values ??= new \WeakMap();
        self::$values[$this] = $value;
    }

    public function reveal(): string
    {
        $value = self::$values[$this] ?? null;
        if (!is_string($value)) {
            throw new \LogicException('This Secret was not created by its constructor');
        }

        return $value;
    }

    #[\Override]
    public function __toString(): string
    {
        return self::MASK;
    }

    /**
     * @return array{value: string}
     */
    public function __debugInfo(): array
    {
        return ['value' => self::MASK];
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        throw new \LogicException('A Secret cannot be serialized');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        throw new \LogicException('A Secret cannot be unserialized');
    }

    private function __clone()
    {
    }
}
