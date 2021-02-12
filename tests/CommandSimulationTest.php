<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tests;

use OpenEuropa\TaskRunner\TaskRunner;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests command simulation.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CommandSimulationTest extends AbstractTest
{
    /**
     * Runs simulations of commands and checks the command output.
     *
     * Robo allows tasks to be simulated. If a command is executed with the
     * `--simulate` option, then instead of actually performing the tasks that
     * are included in the command, Robo will output the parameters that have
     * been passed in.
     *
     * This test is used for commands that pass data through to tasks that are
     * provided by third parties. By using the simulator we can assert that we
     * are passing the right parameters. This is where our responsibility ends.
     *
     * Custom commands may reuse this test by overriding the data provider.
     *
     * @see \Robo\Task\Simulator
     *
     * @param string $command
     *   The command to test, including any command line arguments and options.
     * @param array $config
     *   Configuration in YAML format that will be provided to the command being
     *   tested, as provided by `runner.yml`.
     * @param string $composer
     *   Composer manifest in JSON format. This can be used to test the output
     *   of commands that read data from `composer.json`.
     * @param array $expected
     *   An array of strings that are expected to be present in the simulated
     *   output.
     * @param array $absent
     *   An optional array of strings that are expected to be absent in the
     *   simulated output.
     *
     * @dataProvider simulationDataProvider
     */
    public function testSimulation($command, array $config, $composer, array $expected, array $absent = [])
    {
        $configFile = $this->getSandboxFilepath('runner.yml');
        $composerFile = $this->getSandboxFilepath('composer.json');

        file_put_contents($configFile, Yaml::dump($config));
        file_put_contents($composerFile, $composer);

        $input = new StringInput("{$command} --simulate --working-dir=" . $this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());
        $runner->run();

        $text = $output->fetch();
        foreach ($expected as $row) {
            $this->assertContains($row, $text);
        }
        foreach ($absent as $row) {
            $this->assertNotContains($row, $text);
        }
    }

    /**
     * @return array
     */
    public function simulationDataProvider()
    {
        return $this->getFixtureContent('simulation.yml');
    }
}
