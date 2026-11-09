<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Generate;

use Ashio\OpenProvider\Tools\Spec\SpecException;

/**
 * The PHP names of the spec: definition → class, API key → property, enum value → constant.
 *
 * Every rule is a pure function of the spec name, so a new definition never renames an existing
 * class. Two spec names that would share a PHP name (case-insensitively, as PHP and some file
 * systems compare them) are refused instead of one silently replacing the other.
 */
final class NameMap
{
    public const NAMESPACE = 'Ashio\OpenProvider\Model';

    /**
     * The namespace of each spec service, spelled by hand to keep the API's word boundaries
     * (easydmarc → EasyDmarc). A new service fails generation until it is added here.
     */
    private const SERVICES = [
        'auth' => 'Auth',
        'billing' => 'Billing',
        'dns' => 'Dns',
        'domain' => 'Domain',
        'easydmarc' => 'EasyDmarc',
        'email-template' => 'EmailTemplate',
        'license' => 'License',
        'mailcow' => 'Mailcow',
        'portal' => 'Portal',
        'reseller-customer' => 'ResellerCustomer',
        'spam-expert' => 'SpamExpert',
        'ssl' => 'Ssl',
    ];

    /**
     * Lower-cased names a class cannot take: PHP's reserved words, and the two names every
     * generated model imports.
     */
    private const RESERVED_CLASSES = [
        'abstract', 'and', 'array', 'as', 'bool', 'break', 'callable', 'case', 'catch', 'class', 'clone',
        'const', 'continue', 'declare', 'default', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare',
        'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'enum', 'eval', 'exit', 'extends', 'false',
        'final', 'finally', 'float', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements',
        'include', 'include_once', 'instanceof', 'insteadof', 'int', 'interface', 'isset', 'iterable', 'list',
        'match', 'mixed', 'namespace', 'never', 'new', 'null', 'numeric', 'object', 'or', 'parent', 'print',
        'private', 'protected', 'public', 'readonly', 'require', 'require_once', 'resource', 'return', 'self',
        'static', 'string', 'switch', 'throw', 'trait', 'true', 'try', 'unset', 'use', 'var', 'void', 'while',
        'xor', 'yield',
        'fields', 'model',
    ];

    /** Lower-cased: the property every model has for what its typed properties cannot hold, and $this. */
    private const RESERVED_PROPERTIES = ['extra', 'this'];

    /** `VALUES` lists every value of an enum class; `class` cannot name a constant. */
    private const RESERVED_CONSTANTS = ['VALUES', 'CLASS'];

    /**
     * @param list<string> $definitions
     *
     * @return array<string, string> definition => fully qualified class name
     */
    public static function classes(array $definitions): array
    {
        $classes = [];
        $owners = [];
        foreach ($definitions as $definition) {
            $class = self::className($definition);
            $folded = strtolower($class);
            if (isset($owners[$folded])) {
                throw new SpecException(sprintf('%s both map to the class %s', self::pair($owners[$folded], $definition), $class));
            }
            $owners[$folded] = $definition;
            $classes[$definition] = $class;
        }

        return $classes;
    }

    /**
     * The file of a class, relative to src/Model.
     */
    public static function path(string $class): string
    {
        if (!str_starts_with($class, self::NAMESPACE . '\\')) {
            throw new SpecException(sprintf('%s is not under %s', $class, self::NAMESPACE));
        }

        return str_replace('\\', '/', substr($class, strlen(self::NAMESPACE) + 1)) . '.php';
    }

    /**
     * camelCase; a word in capitals (`AUD`, `DNSSEC`) is lowered first.
     *
     * @param list<string> $keys
     *
     * @return array<string, string> API key => property name
     */
    public static function properties(string $definition, array $keys): array
    {
        $names = [];
        $owners = [];
        foreach ($keys as $key) {
            $name = '';
            foreach (self::words($key) as $i => $word) {
                $word = strtoupper($word) === $word ? strtolower($word) : $word;
                $name .= $i === 0 ? lcfirst($word) : ucfirst($word);
            }
            if (preg_match('/^[a-z][A-Za-z0-9]*$/', $name) !== 1) {
                throw new SpecException(sprintf('%s.%s: "%s" is not a valid property name', $definition, $key, $name));
            }
            $folded = strtolower($name);
            if (in_array($folded, self::RESERVED_PROPERTIES, true)) {
                throw new SpecException(sprintf('%s.%s: the property name $%s is reserved', $definition, $key, $name));
            }
            if (isset($owners[$folded])) {
                throw new SpecException(sprintf('%s: %s both map to the property $%s', $definition, self::pair($owners[$folded], $key), $name));
            }
            $owners[$folded] = $key;
            $names[$key] = $name;
        }

        return $names;
    }

    /**
     * UPPER_SNAKE_CASE; a value that starts with a digit gets a `V_` prefix.
     *
     * @param list<string> $values
     *
     * @return array<string, string> enum value => constant name
     */
    public static function constants(string $definition, array $values): array
    {
        $names = [];
        $owners = [];
        foreach ($values as $value) {
            $name = strtoupper(implode('_', self::words($value)));
            if ($name === '') {
                throw new SpecException(sprintf('%s: "%s" has no letter or digit to make a constant name', $definition, $value));
            }
            if (ctype_digit($name[0])) {
                $name = 'V_' . $name;
            }
            if (in_array($name, self::RESERVED_CONSTANTS, true)) {
                throw new SpecException(sprintf('%s: the constant name %s (for "%s") is reserved', $definition, $name, $value));
            }
            if (isset($owners[$name])) {
                throw new SpecException(sprintf('%s: %s both map to the constant %s', $definition, self::pair($owners[$name], $value), $name));
            }
            $owners[$name] = $value;
            $names[$value] = $name;
        }

        return $names;
    }

    private static function className(string $definition): string
    {
        $parts = explode('.', $definition, 2);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new SpecException(sprintf('The definition name "%s" is not <service>.<name>', $definition));
        }
        $service = self::SERVICES[$parts[0]] ?? throw new SpecException(sprintf(
            '%s: the service "%s" has no namespace in %s::SERVICES',
            $definition,
            $parts[0],
            self::class,
        ));
        $short = implode('', array_map(ucfirst(...), self::words($parts[1])));
        if (preg_match('/^[A-Z][A-Za-z0-9]*$/', $short) !== 1) {
            throw new SpecException(sprintf('%s: "%s" is not a valid class name', $definition, $short));
        }
        if (in_array(strtolower($short), self::RESERVED_CLASSES, true)) {
            throw new SpecException(sprintf('%s: the class name %s is reserved', $definition, $short));
        }

        return self::NAMESPACE . '\\' . $service . '\\' . $short;
    }

    /**
     * @return list<string> the runs of letters and digits
     */
    private static function words(string $name): array
    {
        return preg_split('/[^A-Za-z0-9]+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /**
     * Both names, in a fixed order whatever the input order.
     */
    private static function pair(string $a, string $b): string
    {
        return strcmp($a, $b) < 0 ? "$a and $b" : "$b and $a";
    }
}
