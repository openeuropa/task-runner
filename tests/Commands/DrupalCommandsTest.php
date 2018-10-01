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
     *
     * @dataProvider drupalSettingsDataProvider
     */
    public function testPermissions(array $config)
    {
        $configFile = $this->getSandboxFilepath('runner.yml');
        file_put_contents($configFile, Yaml::dump($config));

        $sitesSubdir = $this->getSandboxFilepath('build/sites/default/');
        mkdir($sitesSubdir, 0777, true);

        touch($sitesSubdir . 'settings.php');

        $input = new StringInput("drupal:permissions-setup --working-dir=".$this->getSandboxRoot());
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $runner->run();

        $sitesSubdirPermissions = substr(sprintf('%o', fileperms($sitesSubdir)), -4);

        $this->assertEquals('0775', $sitesSubdirPermissions);
    }

    /**
     * @return array
     */
    public function drupalSettingsDataProvider()
    {
        return $this->getFixtureContent('commands/drupal-site-install.yml');
    }
}
