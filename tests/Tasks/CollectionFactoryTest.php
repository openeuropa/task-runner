<?php

namespace OpenEuropa\TaskRunner\Tests\Tasks;

use OpenEuropa\TaskRunner\Tasks\CollectionFactory\loadTasks;
use OpenEuropa\TaskRunner\Tests\AbstractTaskTest;
use Robo\Config\Config;

/**
 * Class CollectionFactoryTest
 *
 * @package OpenEuropa\TaskRunner\Tests\Tasks
 */
class CollectionFactoryTest extends AbstractTaskTest
{
    use loadTasks;

    /**
     * Test dynamic "append" task.
     */
    public function testAppendTask()
    {
        $targetFile = $this->getSandboxFilepath('target.txt');
        file_put_contents($targetFile, "Target file");

        $tasks = [];
        $tasks[] = [
            'task' => 'append',
            'file' => $targetFile,
            'text' => ': ${drupal.root}',
        ];
        $this->taskCollectionFactory($tasks)->run();
        $this->assertEquals('Target file: build', file_get_contents($targetFile));
    }

    /**
     * Test dynamic "process-php" task.
     *
     * @param string $type
     * @param string $source
     * @param string $expected
     *
     * @dataProvider processPhpTaskDataProvider
     */
    public function testProcessPhpTask($type, $source, $expected)
    {
        $sourceFile = $this->getSandboxFilepath('default.settings.php');
        $destinationFile = $this->getSandboxFilepath('settings.php');
        file_put_contents($sourceFile, $source);

        $tasks = [];
        $tasks[] = [
          'task' => 'process-php',
          'config' => 'drupal.drush',
          'type' => $type,
          'source' => $sourceFile,
          'destination' => $destinationFile,
        ];

        $this->taskCollectionFactory($tasks)->run();
        $this->assertEquals(trim($expected), trim(file_get_contents($destinationFile)));
    }

    /**
     * @return array
     */
    public function processPhpTaskDataProvider()
    {
        return $this->getFixtureContent('tasks/process-php.yml');
    }
}
