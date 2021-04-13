<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tests\Commands;

use OpenEuropa\TaskRunner\TaskRunner;
use OpenEuropa\TaskRunner\Tests\AbstractTest;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests the `drupal:*` commands.
 */
class DrupalCommandsTest extends AbstractTest
{
    /**
     * @param array $config
     *   The configuration array to pass to the command.
     * @param string $command
     *   The command to execute.
     * @param bool $expected_error
     *   Whether or not it is expected that the command will return an error
     *   code.
     * @param string $expected_settings_dir_permission
     *   A string representing the octal permission number that is expected to
     *   be applied on the settings folder.
     * @param string $expected_settings_file_permission
     *   A string representing the octal permission number that is expected to
     *   be applied on the settings file.
     *
     * @dataProvider drupalSettingsDataProvider
     */
    public function testPermissions(
        array $config,
        $command,
        $expected_error,
        $expected_settings_dir_permission,
        $expected_settings_file_permission
    ) {
        $configFile = $this->getSandboxFilepath('runner.yml');
        file_put_contents($configFile, Yaml::dump($config));

        // Prepare site directory.
        $sitesSubdir = $this->getSandboxFilepath('build/sites/default/');
        mkdir($sitesSubdir, 0777, true);

        // Prepare site settings file.
        $siteSettings = $sitesSubdir . 'settings.php';
        touch($siteSettings);

        // Make the settings folder and file unwritable so we can detect whether
        // the exception is thrown in `DrupalCommands::validateSiteInstall()` as
        // well as whether the permissions are correctly set.
        chmod($siteSettings, 0444);
        chmod($sitesSubdir, 0555);

        // Run command.
        $input = new StringInput("$command --working-dir=" . $this->getSandboxRoot());
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $exit_code = $runner->run();

        // Check if an error is returned when this is expected.
        $this->assertEquals($expected_error, $exit_code != 0);

        // Check site directory.
        $sitesSubdirPermissions = substr(sprintf('%o', fileperms($sitesSubdir)), -4);
        $this->assertEquals($expected_settings_dir_permission, $sitesSubdirPermissions);

        // Check site settings file.
        $siteSettingsPermissions = substr(sprintf('%o', fileperms($siteSettings)), -4);
        $this->assertEquals($expected_settings_file_permission, $siteSettingsPermissions);
    }

    /**
     * @return array
     */
    public function drupalSettingsDataProvider()
    {
        return $this->getFixtureContent('commands/drupal-site-install.yml');
    }
}
