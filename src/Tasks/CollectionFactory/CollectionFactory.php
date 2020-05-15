<?php

namespace OpenEuropa\TaskRunner\Tasks\CollectionFactory;

use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\SimulatedInterface;
use Robo\Exception\TaskException;
use Robo\LoadAllTasks;
use OpenEuropa\TaskRunner as TaskRunner;
use Robo\Task\BaseTask;
use Symfony\Component\Yaml\Yaml;

/**
 * Class CollectionFactory
 *
 * Return a task collection given its array representation.
 *
 * @package OpenEuropa\TaskRunner\Tasks\YamlTaskFacorty
 */
class CollectionFactory extends BaseTask implements BuilderAwareInterface, SimulatedInterface
{
    use LoadAllTasks;
    use TaskRunner\Tasks\ProcessConfigFile\loadTasks;
    use \NuvoleWeb\Robo\Task\Config\Php\loadTasks;

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
     * {@inheritdoc}
     */
    public function run()
    {
        $collection = $this->collectionBuilder();

        foreach ($this->getTasks() as $task) {
            $collection->addTask($this->taskFactory($task));
        }

        return $collection->run();
    }

    /**
     * {@inheritdoc}
     */
    public function simulate($context)
    {
        foreach ($this->getTasks() as $task) {
            if (is_array($task)) {
                $task = Yaml::dump($task, 0);
            }
            $this->printTaskInfo($task, $context);
        }
    }

    /**
     * @return array
     */
    public function getTasks()
    {
        return isset($this->tasks['tasks']) ? $this->tasks['tasks'] : $this->tasks;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return isset($this->tasks['help']) ? $this->tasks['help'] : "Dynamic command defined in runner.yml";
    }

    /**
     * @param array|string $task
     *
     * @return \Robo\Contract\TaskInterface
     *
     * @throws \Robo\Exception\TaskException
     *
     * @SuppressWarnings(PHPMD)
     *
     * @todo: Tuner this into a proper plugin system.
     */
    protected function taskFactory($task)
    {
        if (is_string($task)) {
            return $this->taskExec($task);
        }

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
                if (is_dir($task['from'])) {
                    return $this->taskCopyDir([$task['from'] => $task['to']]);
                }

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

            case "append":
                return $this->collectionBuilder()->addTaskList([
                    $this->taskWriteToFile($task['file'])->append()->text($task['text']),
                    $this->taskProcessConfigFile($task['file'], $task['file']),
                ]);

            case "run":
                $taskExec = $this->taskExec($this->getConfig()->get('runner.bin_dir').'/run')
                    ->arg($task['command'])
                    ->interactive($this->isTtySupported());
                if (!empty($task['arguments'])) {
                    $taskExec->args($task['arguments']);
                }
                if (!empty($task['options'])) {
                    $taskExec->options($task['options'], '=');
                }
                return $taskExec;

            case "process-php":
                $this->secureOption($task, 'override', false);

                // If we don't override destination file simply exit here.
                if (!$task['override'] && file_exists($task['destination'])) {
                    return $this->collectionBuilder();
                }

                // Copy source file to destination before processing it.
                $tasks[] = $this->taskFilesystemStack()->copy($task['source'], $task['destination'], true);

                // Map dynamic task type to actual task callback.
                $map = [
                    'append' => "taskAppendConfiguration",
                    'prepend' => "taskPrependConfiguration",
                    'write' => "taskWriteConfiguration",
                ];

                if (!isset($map[$task['type']])) {
                    throw new TaskException($this, "'process-php' task type '{$task['type']}' is not supported, valid values are: 'append', 'prepend' and 'write'.");
                }
                $method = $map[$task['type']];

                // Add selected process task and return collection.
                $tasks[] = $this->{$method}($task['destination'], $this->getConfig())
                    ->setConfigKey($task['config']);

                return $this->collectionBuilder()->addTaskList($tasks);

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

    /**
     * Checks if the TTY mode is supported
     *
     * @return bool
     */
    protected function isTtySupported()
    {
        return PHP_OS !== 'WINNT' && (bool) @proc_open('echo 1 >/dev/null', [['file', '/dev/tty', 'r'], ['file', '/dev/tty', 'w'], ['file', '/dev/tty', 'w']], $pipes);
    }
}
