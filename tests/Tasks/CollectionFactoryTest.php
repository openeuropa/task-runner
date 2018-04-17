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
}
