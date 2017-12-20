<?php

namespace EC\OpenEuropa\TaskRunner\Tests\Commands;

use EC\OpenEuropa\TaskRunner\TaskRunner;
use EC\OpenEuropa\TaskRunner\Tests\AbstractTest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DrupalCommandsTest.
 *
 * @package EC\OpenEuropa\TaskRunner\Tests\Commands
 */
class CommandsTest extends AbstractTest
{
    /**
     * @param string $command
     * @param array  $config
     * @param array  $expected
     *
     * @dataProvider commandsDataProvider
     */
    public function testSiteInstall($command, array $config, array $expected)
    {
        $configFile = __DIR__.'/sandbox/runner.test.yml';
        file_put_contents($configFile, Yaml::dump($config));
        $input = new StringInput("{$command} --simulate");
        $output = new BufferedOutput();
        $runner = new TaskRunner([$configFile], $input, $output);
        $runner->run();

        $text = $output->fetch();
        foreach ($expected as $row) {
            $this->assertContains($row, $text);
        }
    }

    /**
     * @return array
     */
    public function commandsDataProvider()
    {
        $data = [];
        $finder = new Finder();
        $finder->files()->name('*.yml')->in(__DIR__.'/fixtures/commands');
        foreach ($finder as $file) {
            $data = array_merge($data, Yaml::parse($file->getContents()));
        }

        return $data;
    }
}
