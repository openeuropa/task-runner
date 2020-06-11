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
     * Returns the Robo task for a given task definition.
     *
     * For the moment this is a hardcoded mapping of supported tasks.
     *
     * @param array|string $task
     *   A task definition array consisting of the task name and optionally a
     *   number of configuration options. Can also be a string representing a
     *   shell command.
     *
     * @return \Robo\Contract\TaskInterface
     *   The Robo task.
     *
     * @throws \Robo\Exception\TaskException
     *
     * @SuppressWarnings(PHPMD)
     *
     * @todo: Turn this into a proper plugin system.
     */
    protected function taskFactory($task)
    {
        if (is_string($task)) {
            @trigger_error('Defining a task as a plain text is deprecated in openeuropa/task-runner:1.0.0 and is removed from openeuropa/task-runner:2.0.0. Use the "exec" task and pass arguments and options.', E_USER_DEPRECATED);
            return $this->taskExec($task)->interactive($this->isTtySupported());
        }

        // Set a number of options to safe defaults if they have not been given
        // a different value in the task definition.
        // @todo Not all of these options apply to all available tasks. Only
        //   set defaults for this task's options.
        $this->secureOption($task, 'force', false);
        $this->secureOption($task, 'umask', 0000);
        $this->secureOption($task, 'recursive', false);
        $this->secureOption($task, 'time', time());
        $this->secureOption($task, 'atime', time());
        $this->secureOption($task, 'mode', 0755);

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
                return $this->taskFilesystemStack()->chgrp($task['file'], $task['group'], $task['recursive']);

            case "chown":
                return $this->taskFilesystemStack()->chown($task['file'], $task['user'], $task['recursive']);

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

            case 'exec':
                $taskExec = $this->taskExec($task['command'])->interactive($this->isTtySupported());
                if (!empty($task['arguments'])) {
                    $taskExec->args($task['arguments']);
                }
                if (!empty($task['options'])) {
                    $taskExec->options($task['options']);
                }
                if (!empty($task['dir'])) {
                    $taskExec->dir($task['dir']);
                }
                return $taskExec;

            default:
                throw new TaskException($this, "Task '{$task['task']}' not supported.");
        }
    }

    /**
     * Sets the given safe default value for the option with the given name.
     *
     * If the option is already set it will not be overwritten.
     *
     * @param array  $task
     *   The task array containing the task name and configuration.
     * @param string $name
     *   The name of the option for which to provide a safe default value.
     * @param mixed  $default
     *   The default value.
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
