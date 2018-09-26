<?php

namespace OpenEuropa\TaskRunner\Tests\Commands;

use OpenEuropa\TaskRunner\Commands\ChangelogCommands;
use OpenEuropa\TaskRunner\TaskRunner;
use OpenEuropa\TaskRunner\Tests\AbstractTest;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DrupalCommandsTest.
 *
 * @package OpenEuropa\TaskRunner\Tests\Commands
 */
class CommandsTest extends AbstractTest
{
    /**
     * @param string $command
     * @param array  $config
     * @param string $composer
     * @param array  $expected
     *
     * @dataProvider simulationDataProvider
     */
    public function testSimulation($command, array $config, $composer, array $expected)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');
        $composerFile = $this->getSandboxFilepath('composer.json');

        file_put_contents($configFile, Yaml::dump($config));
        file_put_contents($composerFile, $composer);

        $input = new StringInput("{$command} --simulate --working-dir=".$this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());
        $runner->run();

        $text = $output->fetch();
        foreach ($expected as $row) {
            $this->assertContains($row, $text);
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

        $input = new StringInput("{$command} --working-dir=".$this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());
        $runner->run();

        $actual = file_get_contents($destination);
        $this->assertEquals($expected, $actual);
    }
    /**
     * @param array $config
     * @param array $expected
     *
     * @dataProvider settingsSetupDataProvider
     */
    public function testSettingsPermissions(array $config, array $expected)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');

        file_put_contents($configFile, Yaml::dump($config));

        $input = new StringInput("drupal:settings-setup --working-dir=".$this->getSandboxRoot());
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $runner->run();

        print_r($config);
        $subdir = $config['drupal']['settings']['sites-subdir'];
        $settings = $subdir . '/' . $config['drupal']['settings']['settings_file'];
        print ("Name = $settings\n");
        $this->assertContains('default', $subdir);
        $filePermission = substr(sprintf('%o', fileperms($settings)), - 4);
        $this->assertEquals('0777', $filePermission);
        $this->assertEquals('good', 'bad');
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

        $input = new StringInput("drupal:drush-setup --working-dir=".$this->getSandboxRoot());
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
     * @dataProvider settingsSetupDataProvider
     */
    public function testSettingsSetup(array $config, array $expected)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');

        file_put_contents($configFile, Yaml::dump($config));

        $sites_subdir = isset($config['drupal']['site']['sites_subdir']) ? $config['drupal']['site']['sites_subdir'] : 'default';
        mkdir($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/', 0777, true);
        file_put_contents($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/default.settings.php', '');

        $input = new StringInput("drupal:settings-setup --working-dir=".$this->getSandboxRoot());
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $runner->run();

        foreach ($expected as $row) {
            $content = file_get_contents($this->getSandboxFilepath($row['file']));
            $this->assertContainsNotContains($content, $row);
        }

        // Generate a random function name.
        $fct = $this->generateRandomString(20);

        // Generate a dummy PHP code.
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

        $sites_subdir = isset($config['drupal']['site']['sites_subdir']) ? $config['drupal']['site']['sites_subdir'] : 'default';
        mkdir($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/', 0777, true);
        file_put_contents($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/default.settings.php', '');
        file_put_contents($this->getSandboxRoot() . '/build/sites/' . $sites_subdir . '/settings.php', '# Already existing file.');

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

        // Generate a dummy PHP code.
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

        $input = new StringInput("list --working-dir=".$this->getSandboxRoot());
        $runner = new TaskRunner($input, new NullOutput(), $this->getClassLoader());
        $runner->run();

        $this->assertContains('/tests/sandbox', $runner->getConfig()->get('runner.working_dir'));
        $this->assertContains('/tests/sandbox', $runner->getConfig()->get('working_dir'));
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
    public function settingsSetupDataProvider()
    {
        return $this->getFixtureContent('commands/drupal-settings-setup.yml');
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
