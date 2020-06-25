<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tests\Services;

use OpenEuropa\TaskRunner\Services\Composer;
use OpenEuropa\TaskRunner\Tests\AbstractTest;

/**
 * Tests the parsing of a composer.json file.
 */
class ComposerTest extends AbstractTest
{
    /**
     * @param string $content
     * @param array  $assertions
     *
     * @dataProvider parsingDataProvider
     */
    public function testComposerParsing($content, array $assertions)
    {
        $filepath = $this->getSandboxFilepath('composer.json');
        file_put_contents($filepath, $content);

        $service = new Composer(dirname($filepath));
        foreach ($assertions as $method => $expected) {
            $this->assertEquals($expected, $service->{$method}());
        }
    }

    /**
     * @return array
     */
    public function parsingDataProvider()
    {
        return $this->getFixtureContent('services/composer.yml');
    }
}
