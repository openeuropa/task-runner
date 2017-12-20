<?php

namespace EC\OpenEuropa\TaskRunner\Tests\Tasks;

use EC\OpenEuropa\TaskRunner\Tasks\ReplaceConfigTokens\ReplaceConfigTokens;
use EC\OpenEuropa\TaskRunner\Tests\AbstractTaskTest;

/**
 * Class ReplaceConfigTokensTaskTest.
 *
 * @package EC\OpenEuropa\TaskRunner\Tests\Tasks
 */
class ReplaceConfigTokensTaskTest extends AbstractTaskTest
{
    use \EC\OpenEuropa\TaskRunner\Tasks\ReplaceConfigTokens\loadTasks;

    /**
     * Test task.
     */
    public function testTask()
    {
        $result = $this->taskReplaceConfigTokens()->run();
    }

    /**
     * @param string $text
     * @param array  $expected
     *
     * @dataProvider extractTokensDataProvider
     */
    public function testExtractTokens($text, array $expected)
    {
        $task = new ReplaceConfigTokens();
        $actual = $this->invokeMethod($task, 'extractTokens', [$text]);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function extractTokensDataProvider()
    {
        return $this->getFixtureContent('tasks/replace-config-tokens/extract.yml');
    }
}
