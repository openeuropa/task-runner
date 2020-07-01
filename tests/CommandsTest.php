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
 * Tests various commands.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CommandsTest extends AbstractTest
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
        $runner = new TaskRunner(new StringInput(''), new NullOutput(), $this->getClassLoader());
        /** @var ChangelogCommands $commands */
        $commands = $runner->getCommands(ChangelogCommands::class);
        $this->assertEquals($expected, $commands->generateChangelog($options)->getCommand());
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
     * @param array $config
     * @param array $expected
     *
     * @dataProvider drushSetupDataProvider
     */
    public function testDrushSetup(array $config, array $expected)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');

        file_put_contents($configFile, Yaml::dump($config));

        $input = new StringInput("drupal:drush-setup --working-dir=" . $this->getSandboxRoot());
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $runner->run();

        foreach ($expected as $row) {
            $content = file_get_contents($this->getSandboxFilepath($row['file']));
            $this->assertContainsNotContains($content, $row);
        }
    }

    /**
     * @param array $config
     * @param array $expected
     *
     * @dataProvider drupal7SettingsSetupDataProvider
     */
    public function testDrupal7SettingsSetup(array $config, array $expected)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');

        file_put_contents($configFile, Yaml::dump($config));

        $sites_subdir = $config['drupal']['site']['sites_subdir'] ?? 'default';
        mkdir($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/', 0777, true);
        file_put_contents($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/default.settings.php', '');

        $input = new StringInput('drupal:settings-setup --working-dir=' . $this->getSandboxRoot());
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $runner->run();

        foreach ($expected as $row) {
            $content = file_get_contents($this->getSandboxFilepath($row['file']));
            $this->assertContainsNotContains($content, $row);
        }

        // Generate a random function name.
        $fct = $this->generateRandomString(20);

        // Generate dummy PHP code.
        $config_override_dummy_script = <<< EOF
<?php
function $fct() {}
EOF;

        $config_override_filename = isset($config['drupal']['site']['settings_override_file']) ?
        $config['drupal']['site']['settings_override_file'] :
        'settings.override.php';

        // Add the dummy PHP code to the config override file.
        file_put_contents(
            $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/' . $config_override_filename,
            $config_override_dummy_script
        );

        // Include the config override file.
        include_once $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/' . $config_override_filename;

        // Test if the dummy PHP code has been properly included.
        $this->assertTrue(\function_exists($fct));
    }

    /**
     * @param array $config
     * @param array $expected
     *
     * @dataProvider drupal8SettingsSetupDataProvider
     */
    public function testDrupal8SettingsSetup(array $config, array $expected)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');

        file_put_contents($configFile, Yaml::dump($config));

        $sites_subdir = $config['drupal']['site']['sites_subdir'] ?? 'default';
        mkdir($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/', 0777, true);
        file_put_contents($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/default.settings.php', '');

        $input = new StringInput('drupal:settings-setup --working-dir=' . $this->getSandboxRoot());
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $runner->run();

        foreach ($expected as $row) {
            $content = file_get_contents($this->getSandboxFilepath($row['file']));
            $this->assertContainsNotContains($content, $row);
        }

        // Generate a random function name.
        $fct = $this->generateRandomString(20);

        // Generate dummy PHP code.
        $config_override_dummy_script = <<< EOF
<?php
function $fct() {}
EOF;

        $config_override_filename = isset($config['drupal']['site']['settings_override_file']) ?
        $config['drupal']['site']['settings_override_file'] :
        'settings.override.php';

        // Add the dummy PHP code to the config override file.
        file_put_contents(
            $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/' . $config_override_filename,
            $config_override_dummy_script
        );

        // Include the config override file.
        include_once $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/' . $config_override_filename;

        // Test if the dummy PHP code has been properly included.
        $this->assertTrue(\function_exists($fct));
    }

    /**
     * @param array $config
     * @param array $expected
     *
     * @dataProvider settingsSetupForceDataProvider
     */
    public function testSettingsSetupForce(array $config, array $expected)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');
        file_put_contents($configFile, Yaml::dump($config));

        $sites_subdir = $config['drupal']['site']['sites_subdir'] ?? 'default';
        mkdir($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/', 0777, true);
        $filename = $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/default.settings.php';
        file_put_contents($filename, '');
        $filename = $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/settings.php';
        file_put_contents($filename, '# Already existing file.');

        $input = new StringInput('drupal:settings-setup --working-dir=' . $this->getSandboxRoot());

        if (true === $config['drupal']['site']['force']) {
            $input = new StringInput('drupal:settings-setup --working-dir=' . $this->getSandboxRoot() . ' --force');
        }
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $runner->run();

        foreach ($expected as $row) {
            $content = file_get_contents($this->getSandboxFilepath($row['file']));
            $this->assertContainsNotContains($content, $row);
        }

        // Generate a random function name.
        $fct = $this->generateRandomString(20);

        // Generate dummy PHP code.
        $config_override_dummy_script = <<< EOF
<?php
function $fct() {}
EOF;

        $config_override_filename = isset($config['drupal']['site']['settings_override_file']) ?
        $config['drupal']['site']['settings_override_file'] :
        'settings.override.php';

        // Add the dummy PHP code to the config override file.
        file_put_contents(
            $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/' . $config_override_filename,
            $config_override_dummy_script
        );

        // Include the config override file.
        include_once $this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/' . $config_override_filename;

        // Test if the dummy PHP code has been properly included.
        $this->assertTrue(\function_exists($fct));
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
    public function simulationDataProvider()
    {
        return $this->getFixtureContent('simulation.yml');
    }

    /**
     * @return array
     */
    public function drushSetupDataProvider()
    {
        return $this->getFixtureContent('commands/drupal-drush-setup.yml');
    }

    /**
     * @return array
     */
    public function drupal7SettingsSetupDataProvider()
    {
        return $this->getFixtureContent('commands/drupal7-settings-setup.yml');
    }

    /**
     * @return array
     */
    public function drupal8SettingsSetupDataProvider()
    {
        return $this->getFixtureContent('commands/drupal8-settings-setup.yml');
    }

    /**
     * @return array
     */
    public function settingsSetupForceDataProvider()
    {
        return $this->getFixtureContent('commands/drupal-settings-setup-force.yml');
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
