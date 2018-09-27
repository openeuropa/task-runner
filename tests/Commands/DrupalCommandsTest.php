<?php

namespace OpenEuropa\TaskRunner\Tests\DrupalSettingsPermissions;

use Gitonomy\Git\Commit;
use OpenEuropa\TaskRunner\Services\Time;
use OpenEuropa\TaskRunner\Tests\AbstractTest;
use Gitonomy\Git\Reference;
use Gitonomy\Git\Repository;
use OpenEuropa\TaskRunner\Services\Composer;
use OpenEuropa\TaskRunner\TaskRunner;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;
use PHPUnit\Framework\MockObject\MockObject;

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
