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
     * @param string $config
     *
     * @dataProvider drupalSettingsDataProvider
     */
    public function testDrupalSettingsPermissions($config)
    {
        $subdir = $config['drupal']['settings']['sites-subdir'];
        $settings = $subdir . '/' . $config['drupal']['settings']['settings_file'];
        $this->assertContains('default', $subdir);
        $filePermission = substr(sprintf('%o', fileperms($settings)), - 4);
        $this->assertEquals('0777', $filePermission);

        $configFile = $this->getSandboxFilepath('runner.yml');

        file_put_contents($configFile, Yaml::dump($config));

        $input = new StringInput("drupal:settings-setup --working-dir=".$this->getSandboxRoot());
        $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
        $runner->run();
    }

    /**
     * @return array
     */
    public function drupalSettingsDataProvider()
    {
        return $this->getFixtureContent('simulation.yml');
    }
}
