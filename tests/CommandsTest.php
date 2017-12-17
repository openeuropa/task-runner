<?php

namespace EC\OpenEuropa\TaskRunner\Tests\Commands;

use EC\OpenEuropa\TaskRunner\TaskRunner;
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
class CommandsTest extends TestCase
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
        $runner = new TaskRunner([], new BufferedOutput());
        $input = new StringInput("{$command} --simulate");
        $runner->run($input);

        /** @var \Symfony\Component\Console\Output\BufferedOutput $output */
        $output = $runner->getOutput();
        foreach ($expected as $row) {
            $this->assertContains($row, $output->fetch());
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
