<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tools\Conformance;

use Ashio\OpenProvider\Client\AbstractService;
use Ashio\OpenProvider\Model\ModelMap;
use Ashio\OpenProvider\Tools\Generate\OperationGenerator;
use Ashio\OpenProvider\Tools\Spec\Json;
use Ashio\OpenProvider\Tools\Spec\SpecException;

/**
 * tools/conformance: audits src/Service against spec/openprovider.json and prints every
 * divergence, then the counts. Exits 0 when there is none, 1 when there is any, 2 on bad usage
 * or unreadable input. The operations of a deferred tag group are listed as pending, not failed.
 */
final class ConformanceCommand
{
    /**
     * @param list<string> $arguments
     * @param resource     $out
     * @param resource     $err
     */
    public static function run(array $arguments, string $root, $out, $err): int
    {
        if ($arguments !== []) {
            fwrite($err, "Usage: tools/conformance\n");

            return 2;
        }

        try {
            $spec = Json::object(self::decode($root . '/spec/openprovider.json'), 'spec/openprovider.json');
            $behavior = Json::object(self::decode($root . '/spec/behavior.json'), 'spec/behavior.json');
            $login = Json::object(Json::object($behavior['authentication'] ?? null, 'authentication')['login'] ?? null, 'authentication.login');
            $public = [Json::string($login['operation'] ?? null, 'authentication.login.operation')];
        } catch (SpecException $e) {
            fwrite($err, $e->getMessage() . "\n");

            return 2;
        }

        $report = (new Auditor($spec, self::services($root), ModelMap::MODELS, OperationGenerator::DEFERRED_GROUPS, $public))->run();
        fwrite($report->isClean() ? $out : $err, $report->render());

        return $report->isClean() ? 0 : 1;
    }

    /**
     * Every class in src/Service that extends AbstractService.
     *
     * @return list<class-string<AbstractService>>
     */
    private static function services(string $root): array
    {
        $services = [];
        $files = glob($root . '/src/Service/*.php');
        foreach ($files === false ? [] : $files as $file) {
            $class = 'Ashio\\OpenProvider\\Service\\' . basename($file, '.php');
            if (class_exists($class) && is_subclass_of($class, AbstractService::class)) {
                $services[] = $class;
            }
        }
        sort($services);

        return $services;
    }

    private static function decode(string $file): mixed
    {
        $json = @file_get_contents($file);
        if ($json === false) {
            throw new SpecException('Cannot read ' . $file);
        }

        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new SpecException(sprintf('%s is not valid JSON: %s', $file, $e->getMessage()), 0, $e);
        }
    }
}
