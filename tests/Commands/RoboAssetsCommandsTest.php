<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tests\Commands;

use OpenEuropa\TaskRunner\Commands\ChangelogCommands;
use OpenEuropa\TaskRunner\TaskRunner;
use OpenEuropa\TaskRunner\Tests\AbstractTest;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests the command wrappers for Robo "assets" tasks.
 *
 * @coversDefaultClass \OpenEuropa\TaskRunner\TaskRunner\Commands\RoboAssetsCommands
 */
class RoboAssetsCommandsTest extends AbstractTest
{

    /**
     * @param string $style
     *   The CSS style to be generated.
     * @param string $expected
     *   The expected compiled CSS.
     *
     * @covers ::compileScss
     * @dataProvider compileScssDataProvider
     */
    public function testCompileScss(string $style, string $expected): void
    {
        $command = sprintf(
            'assets:compile-scss --working-dir=%s --style=%s %s %s',
            $this->getSandboxRoot(),
            $style,
            '../fixtures/example.scss',
            'output.css'
        );
        $input = new StringInput($command);
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());
        $runner->run();

        $actual = file_get_contents($this->getSandboxFilepath('output.css'));
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for ::testCompileScss().
     *
     * @return array[]
     *   An array of test cases, each test case an array with two elements:
     *   - A string containing the CSS style to be generated.
     *   - A string containing the expected compiled CSS.
     */
    public function compileScssDataProvider(): array
    {
        return [
            [
                'compact',
                <<<CSS
 nav ul { margin:0; }

 nav ul li { color:#111; }


CSS
            ],
            [
                'compressed',
                <<<CSS
nav ul{margin:0}nav ul li{color:#111}
CSS
            ],
            [
                'crunched',
                <<<CSS
nav ul{margin:0}nav ul li{color:#111}
CSS
            ],
            [
                'expanded',
                <<<CSS
nav ul {
  margin: 0;
}
nav ul li {
  color: #111;
}

CSS
            ],
            [
                'nested',
                <<<CSS
nav ul {
  margin: 0; }
  nav ul li {
    color: #111; }

CSS
            ],
        ];
    }
}
