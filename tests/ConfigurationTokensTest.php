<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tests;

use My\Custom\TestConfigurationTokensTraitWrapper;
use PHPUnit\Framework\TestCase;

/**
 * Tests configuration tokens.
 *
 * @coversDefaultClass \OpenEuropa\TaskRunner\Traits\ConfigurationTokensTrait
 */
class ConfigurationTokensTest extends TestCase
{
    /**
     * @param string $text
     * @param string[] $expectedTokens
     * @covers ::extractRawTokens
     * @dataProvider extractRawTokensDataProvider
     */
    public function testExtractRawTokens(string $text, array $expectedTokens): void
    {
        $class = new \ReflectionClass(TestConfigurationTokensTraitWrapper::class);
        $method = $class->getMethod('extractRawTokens');
        $method->setAccessible(true);
        $actualTokens = array_keys(
            $method->invoke(new TestConfigurationTokensTraitWrapper(), $text)
        );
        $this->assertSame($expectedTokens, $actualTokens);
    }

    /**
     * @return array
     */
    public function extractRawTokensDataProvider(): array
    {
        return [
            'simple' => [
                '${foo.bar.baz} lore {ipsum}...${to.ke.n}',
                ['${foo.bar.baz}', '${to.ke.n}'],
            ],
            'element with single char' => [
                '${foo.b.baz}',
                ['${foo.b.baz}']
            ],
            'with digits' => [
                '${foo1.ba2r5.baz22}',
                ['${foo1.ba2r5.baz22}'],
            ],
            'no digits as 1st char' => [
                '${1foo.bar.baz} ${1foo.2bar.3baz}',
                [],
            ],
            'forbidden chars' => [
                '${foo!@#$%^&*.bar.baz} ${fo-o.b_Ar.ba--z3}',
                ['${fo-o.b_Ar.ba--z3}'],
            ],
        ];
    }
}
