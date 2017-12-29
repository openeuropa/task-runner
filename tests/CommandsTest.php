<?php

namespace EC\OpenEuropa\TaskRunner\Tests\Commands;

use EC\OpenEuropa\TaskRunner\Commands\ChangelogCommands;
use EC\OpenEuropa\TaskRunner\TaskRunner;
use EC\OpenEuropa\TaskRunner\Tests\AbstractTest;
use PHPUnit\Framework\TestCase;
use Robo\Task\Simulator;
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
     * @dataProvider simulationDataProvider
     */
    public function testSimulation($command, array $config, array $expected)
    {
        $configFile = $this->getSandboxPath('runner.test.yml');
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
     * @param string $command
     * @param string $content
     * @param string $expected
     *
     * @dataProvider setupDataProvider
     */
    public function testSetupCommands($command, $content, $expected)
    {
        $source = $this->getSandboxPath('source.yml');
        $destination = $this->getSandboxPath('destination.yml');
        file_put_contents($source, $content);

        $input = new StringInput("{$command} --source={$source} --destination={$destination}");
        $output = new BufferedOutput();
        $runner = new TaskRunner([], $input, $output);
        $runner->run();

        $actual = file_get_contents($destination);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param array  $options
     * @param string $expected
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @dataProvider changelogDataProvider
     */
    public function testChangelogCommands(array $options, $expected)
    {
        $runner = new TaskRunner();
        /** @var ChangelogCommands $commands */
        $commands = $runner->getCommands(ChangelogCommands::class);
        $this->assertEquals($expected, $commands->generateChangelog($options)->getCommand());
    }

    /**
     * @return array
     */
    public function simulationDataProvider()
    {
        return $this->getFixtureContent('simulation.yml');
    }

    /**
     * @return array
     */
    public function setupDataProvider()
    {
        return $this->getFixtureContent('setup.yml');
    }

    /**
     * @return array
     */
    public function changelogDataProvider()
    {
        return $this->getFixtureContent('changelog.yml');
    }
}
