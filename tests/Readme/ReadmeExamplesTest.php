<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Readme;

use Ashio\OpenProvider\Auth\Credentials;
use Ashio\OpenProvider\Client\ApiClient;
use Ashio\OpenProvider\Client\ClientConfig;
use Ashio\OpenProvider\OpenProvider;
use Ashio\OpenProvider\Tests\Support\ArrayCache;
use Ashio\OpenProvider\Tests\Support\SpecOperations;
use Ashio\OpenProvider\Tests\Support\SpecTransport;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * S8: every PHP example of README.md runs, with strict types, against responses built from the
 * spec (SpecTransport). Each example starts from the same variables a reader has after the quick
 * start: `$openProvider`, `$credentials`, `$config`, `$cache` (a PSR-16 cache) and `$httpClient`
 * (a PSR-18 client). An example that builds its own client must not call the API with it, since
 * that client would reach the network.
 */
final class ReadmeExamplesTest extends TestCase
{
    private const README = SpecOperations::ROOT . '/README.md';

    /**
     * @return iterable<string, array{string}>
     */
    public static function examples(): iterable
    {
        foreach (self::blocks() as $line => $code) {
            yield sprintf('README.md:%d', $line) => [$code];
        }
    }

    public function testTheReadmeHasItsExamples(): void
    {
        self::assertGreaterThanOrEqual(15, count(self::blocks()), 'README.md lost its PHP examples, or the parser stopped finding them');
    }

    #[DataProvider('examples')]
    public function testTheExampleRuns(string $code): void
    {
        $transport = new SpecTransport();
        $credentials = new Credentials('readme', 'readme-password');
        $ambient = [
            'openProvider' => new OpenProvider(ApiClient::create(ClientConfig::sandbox(), $credentials, null, $transport)),
            'credentials' => $credentials,
            'config' => ClientConfig::sandbox(),
            'cache' => new ArrayCache(),
            'httpClient' => $transport,
        ];
        $buildsAClient = str_contains($code, 'ApiClient::create(') && !str_contains($code, 'httpClient:');
        $callsTheApi = preg_match('/\$openProvider->[a-zA-Z]+\(\)->/', $code) === 1;
        if ($buildsAClient) {
            self::assertFalse($callsTheApi, 'an example that builds a client with the default transport must not call the API');
        }

        $variables = self::execute($code, $ambient);

        if ($callsTheApi) {
            self::assertNotSame([], $transport->calls(), 'the example calls the API through $openProvider');
        } else {
            self::assertSame(0, $transport->sent(), 'nothing is sent until an operation is called');
        }
        if ($buildsAClient && isset($variables['openProvider'])) {
            self::assertInstanceOf(OpenProvider::class, $variables['openProvider']);
        }
    }

    /**
     * Runs one example as a reader would after the quick start: the opening tag and the autoloader
     * are already there, so they are dropped; the rest runs as written, its output discarded.
     *
     * @param array<string, mixed> $ambient
     *
     * @return array<string, mixed> the variables the example leaves defined
     */
    private static function execute(string $code, array $ambient): array
    {
        $source = (string) preg_replace(['/^<\?php\s*$/m', '/^require .*autoload\.php\';\s*$/m'], '', $code);
        putenv('OPENPROVIDER_USERNAME=readme');
        putenv('OPENPROVIDER_PASSWORD=readme-password');
        ob_start();

        try {
            return (static function (array $__ambient, string $__source): array {
                extract($__ambient);
                eval("declare(strict_types=1);\n" . $__source);

                return get_defined_vars();
            })($ambient, $source);
        } finally {
            ob_end_clean();
            putenv('OPENPROVIDER_USERNAME');
            putenv('OPENPROVIDER_PASSWORD');
        }
    }

    /**
     * The ```php blocks of README.md, keyed by the line they start on.
     *
     * @return array<int, string>
     */
    private static function blocks(): array
    {
        $lines = file(self::README, FILE_IGNORE_NEW_LINES);
        self::assertIsArray($lines);
        $blocks = [];
        $start = null;
        $code = [];
        foreach ($lines as $i => $line) {
            if ($start === null && rtrim($line) === '```php') {
                $start = $i + 1;
                $code = [];
            } elseif ($start !== null && rtrim($line) === '```') {
                $blocks[$start + 1] = implode("\n", $code);
                $start = null;
            } elseif ($start !== null) {
                $code[] = $line;
            }
        }

        return $blocks;
    }
}
