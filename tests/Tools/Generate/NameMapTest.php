<?php

declare(strict_types=1);

namespace Ashio\OpenProvider\Tests\Tools\Generate;

use Ashio\OpenProvider\Tools\Generate\NameMap;
use Ashio\OpenProvider\Tools\Spec\SpecException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NameMapTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function classes(): iterable
    {
        yield 'package prefix kept' => ['domain.domainCreateDomainRequest', 'Ashio\OpenProvider\Model\Domain\DomainCreateDomainRequest'];
        yield 'underscores' => ['dns.ns_groupGroup', 'Ashio\OpenProvider\Model\Dns\NsGroupGroup'];
        yield 'dashed service' => ['spam-expert.se_domainDomain', 'Ashio\OpenProvider\Model\SpamExpert\SeDomainDomain'];
        yield 'service spelled by hand' => ['easydmarc.orderCreateEasyDmarcResponse', 'Ashio\OpenProvider\Model\EasyDmarc\OrderCreateEasyDmarcResponse'];
        yield 'portal' => ['portal.mcWhiteLabel_white_labelRetrieveWhiteLabelDomainRequest', 'Ashio\OpenProvider\Model\Portal\McWhiteLabelWhiteLabelRetrieveWhiteLabelDomainRequest'];
    }

    #[DataProvider('classes')]
    public function testADefinitionMapsToAClassInItsServiceNamespace(string $definition, string $class): void
    {
        self::assertSame([$definition => $class], NameMap::classes([$definition]));
    }

    public function testTheFileFollowsTheClass(): void
    {
        self::assertSame('Dns/NsGroupGroup.php', NameMap::path('Ashio\OpenProvider\Model\Dns\NsGroupGroup'));
    }

    public function testTwoDefinitionsWithTheSameClassAreRefusedEvenWhenOnlyTheCaseDiffers(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('domain.fooBar and domain.foo_bar');

        NameMap::classes(['domain.fooBar', 'domain.foo_bar']);
    }

    public function testCaseOnlyCollisionsAreRefused(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('domain.FooBar and domain.fooBar');

        NameMap::classes(['domain.fooBar', 'domain.FooBar']);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function badDefinitions(): iterable
    {
        yield 'unknown service' => ['newservice.thing', 'service "newservice"'];
        yield 'no service' => ['thing', 'is not <service>.<name>'];
        yield 'reserved word' => ['domain.list', 'reserved'];
        yield 'imported name' => ['domain.fields', 'reserved'];
        yield 'leading digit' => ['domain.2fa', 'class name'];
    }

    #[DataProvider('badDefinitions')]
    public function testDefinitionsThatCannotBeAClassAreRefused(string $definition, string $message): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage($message);

        NameMap::classes([$definition]);
    }

    public function testPropertiesAreCamelCaseAndAllCapsKeysAreLowered(): void
    {
        self::assertSame(
            ['id' => 'id', 'domain_name' => 'domainName', 'ns_1' => 'ns1', 'AUD' => 'aud', 'sslCert' => 'sslCert', 'is_DNSSEC_enabled' => 'isDnssecEnabled'],
            NameMap::properties('x.y', ['id', 'domain_name', 'ns_1', 'AUD', 'sslCert', 'is_DNSSEC_enabled']),
        );
    }

    /**
     * @return iterable<string, array{list<string>, string}>
     */
    public static function badProperties(): iterable
    {
        yield 'collision' => [['name_server', 'nameServer'], 'nameServer and name_server'];
        yield 'case collision' => [['url', 'URL'], 'URL and url'];
        yield 'the extra property' => [['extra'], 'reserved'];
        yield 'this' => [['this'], 'reserved'];
        yield 'leading digit' => [['2fa'], 'property name'];
        yield 'empty key' => [[''], 'property name'];
    }

    /**
     * @param list<string> $keys
     */
    #[DataProvider('badProperties')]
    public function testPropertiesThatCannotBeAPhpPropertyAreRefused(array $keys, string $message): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage($message);

        NameMap::properties('x.y', $keys);
    }

    public function testEnumValuesBecomeConstants(): void
    {
        self::assertSame(
            ['BETWEEN_1_AND_10' => 'BETWEEN_1_AND_10', 'web-hosting' => 'WEB_HOSTING', '2fa' => 'V_2FA'],
            NameMap::constants('x.y', ['BETWEEN_1_AND_10', 'web-hosting', '2fa']),
        );
    }

    /**
     * @return iterable<string, array{list<string>, string}>
     */
    public static function badConstants(): iterable
    {
        yield 'collision' => [['a-b', 'A_B'], 'A_B and a-b'];
        yield 'the VALUES list' => [['values'], 'reserved'];
        yield 'no letters or digits' => [['--'], 'constant name'];
    }

    /**
     * @param list<string> $values
     */
    #[DataProvider('badConstants')]
    public function testEnumValuesThatCannotBeAConstantAreRefused(array $values, string $message): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage($message);

        NameMap::constants('x.y', $values);
    }
}
