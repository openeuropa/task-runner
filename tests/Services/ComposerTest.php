<?php

namespace EC\OpenEuropa\TaskRunner\Tests\Services;

use EC\OpenEuropa\TaskRunner\Services\Composer;
use EC\OpenEuropa\TaskRunner\Tests\AbstractTest;

/**
 * Class ComposerTest
 *
 * @package EC\OpenEuropa\TaskRunner\Tests\Services
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
        $filepath = $this->getSandboxPath('composer.json');
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
