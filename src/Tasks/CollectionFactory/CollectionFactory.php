<?php

namespace EC\OpenEuropa\TaskRunner\Tasks\CollectionFactory;

use EC\OpenEuropa\TaskRunner\Traits\ConfigurationTokensTrait;
use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Exception\TaskException;
use Robo\LoadAllTasks;
use Robo\Task as Task;
use EC\OpenEuropa\TaskRunner as TaskRunner;
use Robo\Task\BaseTask;
use Robo\TaskAccessor;

/**
 * Class CollectionFactory
 *
 * Return a task collection given its array representation.
 *
 * @package EC\OpenEuropa\TaskRunner\Tasks\YamlTaskFacorty
 */
class CollectionFactory extends BaseTask implements BuilderAwareInterface
{
    use LoadAllTasks;
    use TaskRunner\Tasks\ProcessConfigFile\loadTasks;

    /**
     * @var array
     */
    protected $tasks;

    /**
     * CollectionFactory constructor.
     *
     * @param array $tasks
     */
    public function __construct(array $tasks = [])
    {
        $this->tasks = $tasks;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder|\Robo\Result
     * @throws \Robo\Exception\TaskException
     */
    public function run()
    {
        $collection = $this->collectionBuilder();

        foreach ($this->tasks as $task) {
            $collection->addTask($this->taskFactory($task));
        }

        return $collection->run();
    }

    /**
     * @param array $task
     *
     * @return \Robo\Contract\TaskInterface
     *
     * @throws \Robo\Exception\TaskException
     *
     * @SuppressWarnings(PHPMD)
     *
     * @todo: Tuner this into a proper plugin system.
     */
    protected function taskFactory(array $task)
    {
        $this->secureOption($task, 'force', false);
        $this->secureOption($task, 'umask', 0000);
        $this->secureOption($task, 'recursive', false);
        $this->secureOption($task, 'time', time());
        $this->secureOption($task, 'atime', time());
        $this->secureOption($task, 'mode', 0777);

        switch ($task['task']) {
            case "mkdir":
                return $this->taskFilesystemStack()->mkdir($task['dir'], $task['mode']);

            case "touch":
                return $this->taskFilesystemStack()->touch($task['file'], $task['time'], $task['atime']);

            case "copy":
                return $this->taskFilesystemStack()->copy($task['from'], $task['to'], $task['force']);

            case "chmod":
                return $this->taskFilesystemStack()->chmod($task['file'], $task['permissions'], $task['umask'], $task['recursive']);

            case "chgrp":
                return $this->taskFilesystemStack()->chgrp($task['file'], $task['group'], $task['umask'], $task['recursive']);

            case "chown":
                return $this->taskFilesystemStack()->chown($task['file'], $task['user'], $task['umask'], $task['recursive']);

            case "remove":
                return $this->taskFilesystemStack()->remove($task['file']);

            case "rename":
                return $this->taskFilesystemStack()->rename($task['from'], $task['to'], $task['force']);

            case "symlink":
                return $this->taskFilesystemStack()->symlink($task['from'], $task['to']);

            case "mirror":
                return $this->taskFilesystemStack()->mirror($task['from'], $task['to']);

            case "process":
                return $this->taskProcessConfigFile($task['source'], $task['destination']);

            default:
                throw new TaskException($this, "Task '{$task['task']}' not supported.");
        }
    }

    /**
     * Secure option value.
     *
     * @param array  $task
     * @param string $name
     * @param mixed  $default
     */
    protected function secureOption(array &$task, $name, $default)
    {
        $task[$name] = isset($task[$name]) ? $task[$name] : $default;
    }
}
