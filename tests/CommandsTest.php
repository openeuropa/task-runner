<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tests;

use OpenEuropa\TaskRunner\TaskRunner;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests various commands.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CommandsTest extends AbstractTest
{
    /**
     * @param string $command
     * @param string $source
     * @param string $destination
     * @param array  $config
     * @param string $content
     * @param string $expected
     *
     * @dataProvider setupDataProvider
     */
    public function testSetupCommands($command, $source, $destination, array $config, $content, $expected)
    {
        $source = $this->getSandboxFilepath($source);
        $destination = $this->getSandboxFilepath($destination);
        $configFile = $this->getSandboxFilepath('runner.yml');

        file_put_contents($source, $content);
        file_put_contents($configFile, Yaml::dump($config));

        $input = new StringInput("{$command} --working-dir=" . $this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());
        $runner->run();

        $actual = file_get_contents($destination);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test custom commands.
     */
    public function testCustomCommands()
    {
        $input = new StringInput("list");
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());
        $runner->run();

        $expected = [
            "custom:command-four",
            "custom:command-one",
            "custom:command-three",
            "custom:command-two",
        ];

        $text = $output->fetch();
        foreach ($expected as $row) {
            $this->assertContains($row, $text);
        }
    }

    /**
     * Test current working directory as a replaceable token.
     */
    public function testWorkingDirectoryToken()
    {
        $configFile = $this->getSandboxFilepath('runner.yml');
        $config = [
            'working_dir' => '${runner.working_dir}',
        ];
        file_put_contents($configFile, Yaml::dump($config));

        $input = new StringInput("list --working-dir=" . $this->getSandboxRoot());
        $runner = new TaskRunner($input, new NullOutput(), $this->getClassLoader());
        $runner->run();

        $this->assertContains('/tests/sandbox', $runner->getConfig()->get('runner.working_dir'));
        $this->assertContains('/tests/sandbox', $runner->getConfig()->get('working_dir'));
    }

    /**
     * Test the user config.
     */
    public function testUserConfigFile()
    {
        // Create a local config file.
        $runnerYaml = $this->getSandboxRoot() . '/runner.yml';
        file_put_contents($runnerYaml, Yaml::dump(['foo' => 'baz']));

        // Add the environment setting.
        putenv('OPENEUROPA_TASKRUNNER_CONFIG=' . __DIR__ . '/fixtures/userconfig.yml');

        // Create a new runner.
        $input = new StringInput('list --working-dir=' . $this->getSandboxRoot());
        $runner = new TaskRunner($input, new NullOutput(), $this->getClassLoader());

        // Set as `build` by `config/runner.yml`.
        // Overwritten as `drupal` by `tests/fixtures/userconfig.yml`.
        $this->assertEquals('drupal', $runner->getConfig()->get('drupal.root'));

        // Set as `['root' => 'drupal']` by `TestConfigProvider::provide()`.
        // Overwritten as `['root' => 'wordpress']` by `userconfig.yml`.
        $this->assertSame(['root' => 'wordpress'], $runner->getConfig()->get('wordpress'));

        // Set as `['root' => 'joomla']` by `tests/fixtures/third_party.yml`.
        $this->assertSame(['root' => 'joomla'], $runner->getConfig()->get('joomla'));

        // Set as `overwritten by edge case` by `tests/fixtures/userconfig.yml`.
        // Overwritten as `overwritten` by `EdgeCaseConfigProvider::provide()`.
        $this->assertSame('overwritten', $runner->getConfig()->get('whatever'));

        // The `qux` value is computed by using the `${foo}` token. We test
        // that the replacements are done at the very end, when all the config
        // providers had the chance to resolve the tokens. `${foo}` equals
        // `bar`, in the `tests/fixtures/third_party.yml` file but is
        // overwritten at the end, in `tests/sandbox/runner.yml` with `baz`.
        $this->assertSame('is-baz', $runner->getConfig()->get('qux'));
    }

    /**
     * Tests that existing commands can be overridden in the runner config.
     *
     * @dataProvider overrideCommandDataProvider
     *
     * @param string $command
     *   A command that will be executed by the task runner.
     * @param array $runnerConfig
     *   An array of task runner configuration data, equivalent to what would be
     *   written in a "runner.yml" file. This contains the overridden commands.
     * @param array $expected
     *   An array of strings which are expected to be output to the terminal
     *   during execution of the command.
     */
    public function testOverrideCommand($command, array $runnerConfig, array $expected)
    {
        $runnerConfigFile = $this->getSandboxFilepath('runner.yml');
        file_put_contents($runnerConfigFile, Yaml::dump($runnerConfig));

        $input = new StringInput("{$command} --working-dir=" . $this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());
        $exit_code = $runner->run();

        // Check that the command succeeded, i.e. has exit code 0.
        $this->assertEquals(0, $exit_code);

        // Check that the output is as expected.
        $text = $output->fetch();
        foreach ($expected as $row) {
            $this->assertContains($row, $text);
        }
    }

    /**
     * @return array
     */
    public function setupDataProvider()
    {
        return $this->getFixtureContent('setup.yml');
    }

    /**
     * Provides test cases for ::testOverrideCommand().
     *
     * @return array
     *   An array of test cases, each one an array with the following keys:
     *   - 'command': A string representing a command that will be executed by
     *     the task runner.
     *   - 'runnerConfig': An array of task runner configuration data,
     *     equivalent to what would be written in a "runner.yml" file.
     *   - 'expected': An array of strings which are expected to be output to
     *     the terminal during execution of the command.
     *
     * @see \OpenEuropa\TaskRunner\Tests\Commands\CommandsTest::testOverrideCommand()
     */
    public function overrideCommandDataProvider(): array
    {
        return $this->getFixtureContent('override.yml');
    }

    /**
     * @param string $content
     * @param array  $expected
     */
    protected function assertContainsNotContains($content, array $expected)
    {
        $this->assertContains($expected['contains'], $content);
        if (!empty($expected['not_contains'])) {
            $this->assertNotContains($expected['not_contains'], $content);
        }
    }
}
