<?php

namespace OpenEuropa\TaskRunner\Tests\Tasks;

use OpenEuropa\TaskRunner\Tasks\CollectionFactory\loadTasks;
use OpenEuropa\TaskRunner\Tests\AbstractTaskTest;

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
     * @param string    $type
     * @param bool|null $override
     * @param bool      $destinationExists
     * @param string    $source
     * @param string    $expected
     *
     * @dataProvider processPhpTaskDataProvider
     */
    public function testProcessPhpTask($type, $override, $destinationExists, $source, $expected)
    {
        $sourceFile = $this->getSandboxFilepath('default.settings.php');
        $destinationFile = $this->getSandboxFilepath('settings.php');
        file_put_contents($sourceFile, $source);
        @unlink($destinationFile);

        $tasks = [];
        $tasks[] = [
            'task' => 'process-php',
            'config' => 'drupal.drush',
            'type' => $type,
            'source' => $sourceFile,
            'destination' => $destinationFile,
        ];

        // Make sure we test default override option by not setting it if null.
        if ($override !== null) {
            $tasks[0]['override'] = $override;
        }

        // Create destination file before running the task, if required.
        if ($destinationExists) {
            file_put_contents($destinationFile, $source);
        }

        $this->taskCollectionFactory($tasks)->run();
        $this->assertEquals(trim($expected), trim(file_get_contents($destinationFile)));
    }

    /**
     * Tests the 'run' task.
     */
    public function testRunTask()
    {
        $filePath = $this->getSandboxFilepath('test-file.txt');

        $tasks = [];
        $tasks[] = [
            'task' => 'run',
            'command' => 'custom:test',
            'arguments' => [
                __METHOD__,
            ],
            'options' => [
                'filepath' => $filePath,
            ],
        ];
        $this->taskCollectionFactory($tasks)->run();
        $this->assertSame(__METHOD__, file_get_contents($filePath));
    }

    /**
     * Tests the 'exec' task.
     */
    public function testExecTask(): void
    {
        $tasks = [
            [
                'task' => 'exec',
                'command' => 'touch',
                'arguments' => [
                    'file.txt',
                ],
                'options' => [
                    // 1980-06-06 23:59:59.
                    '-t' => '198006062359.59'
                ],
                'dir' => $this->getSandboxRoot(),
            ],
        ];
        $this->taskCollectionFactory($tasks)->run();

        $this->assertFileExists($this->getSandboxFilepath('file.txt'));
        $mtime = gmdate('Y-m-d H:i:s', filemtime($this->getSandboxFilepath('file.txt')));
        $this->assertSame('1980-06-06 23:59:59', $mtime);
    }

    /**
     * @return array
     */
    public function processPhpTaskDataProvider()
    {
        return $this->getFixtureContent('tasks/process-php.yml');
    }
}
