<?php

namespace OpenEuropa\TaskRunner\Tests\Commands;

use OpenEuropa\TaskRunner\Tests\AbstractTest;
use OpenEuropa\TaskRunner\TaskRunner;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DrupalCommandsTest
 *
 * @package OpenEuropa\TaskRunner\Tests\DrupalSettings
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
    public function testPermissions(array $config, $command, $expected_error, $expected_settings_dir_permission, $expected_settings_file_permission)
    {
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
     * Test the services file setup.
     *
     * @param array $config
     * @param array $data
     * @param array $expected
     *
     * @dataProvider servicesSetupDataProvider
     */
    public function testServicesSetup(array $config, array $data, array $expected)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');
        file_put_contents($configFile, Yaml::dump($config));

        // Prepare data for assertions depending if service filename is given in
        // command or in runner Yaml file.
        if (!empty($data['service-parameter'])) {
            $services_source_file = $this->getSandboxFilepath($data['service-parameter']);
            $service_parameter = ' --service-parameters=' . $data['service-parameter'];
        } else {
            $services_source_file = $this->getSandboxFilepath($config['drupal']['service_parameters']);
            $service_parameter = '';
        }

        touch($services_source_file);
        file_put_contents($services_source_file, $data['services']['content']);

        $command = 'drupal:services-setup' . $service_parameter . ' --root=' . $this->getSandboxRoot() . ' --working-dir=' . $this->getSandboxRoot();
        $input = new StringInput($command);
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $runner->run();

        $sites_subdir = isset($config['drupal']['site']['sites_subdir']) ? $config['drupal']['site']['sites_subdir'] : 'default';
        $services_destination_dir = $this->getSandboxRoot() . '/sites/' . $sites_subdir;
        $services_destination_file = $services_destination_dir . '/services.yml';

        foreach ($expected as $row) {
            $content = file_get_contents($services_destination_file);
            $this->assertContains($row['contains'], $content);
        }
    }

    /**
     * @return array
     */
    public function drupalSettingsDataProvider()
    {
        return $this->getFixtureContent('commands/drupal-site-install.yml');
    }

    /**
     * @return array
     */
    public function servicesSetupDataProvider()
    {
        return $this->getFixtureContent('commands/drupal-services-setup.yml');
    }
}
