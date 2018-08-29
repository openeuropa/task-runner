<?php

namespace OpenEuropa\TaskRunner\Tests\Tasks;

use OpenEuropa\TaskRunner\Tasks\ProcessConfigFile\ProcessConfigFile;
use OpenEuropa\TaskRunner\Tests\AbstractTaskTest;
use Robo\Task\Simulator;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ProcessConfigFileTest.
 *
 * @package OpenEuropa\TaskRunner\Tests\Tasks
 */
class ProcessConfigFileTest extends AbstractTaskTest
{
    use \OpenEuropa\TaskRunner\Tasks\ProcessConfigFile\loadTasks;

    /**
     * Test task.
     *
     * @param array $data
     * @param array $expected
     *
     * @dataProvider taskDataProvider
     */
    public function testTask(array $data, array $expected)
    {
        $source = $this->getSandboxFilepath('source.yml');
        $destination = $this->getSandboxFilepath('destination.yml');
        file_put_contents($source, Yaml::dump($data));
        $this->taskProcessConfigFile($source, $destination)->run();
        $destinationData = Yaml::parse(file_get_contents($destination));
        $this->assertEquals($expected, $destinationData);
    }

    /**
     * @param string $text
     * @param array  $expected
     *
     * @dataProvider extractTokensDataProvider
     */
    public function testExtractRawTokens($text, array $expected)
    {
        $task = new ProcessConfigFile(null, null);
        $actual = $this->invokeMethod($task, 'extractRawTokens', [$text]);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function extractTokensDataProvider()
    {
        return $this->getFixtureContent('tasks/process-config-file/extract.yml');
    }

    /**
     * @return array
     */
    public function taskDataProvider()
    {
        return $this->getFixtureContent('tasks/process-config-file/task.yml');
    }
}
