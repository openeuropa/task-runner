<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tasks\ProcessConfigFile;

use OpenEuropa\TaskRunner\Traits\ConfigurationTokensTrait;
use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Exception\TaskException;
use Robo\Task\BaseTask;
use Robo\Task\File\Replace;
use Robo\Task\Filesystem\FilesystemStack;

/**
 * Tasks to process configuration files.
 */
class ProcessConfigFile extends BaseTask implements BuilderAwareInterface
{
    use BuilderAwareTrait;
    use ConfigurationTokensTrait;

    /**
     * Source file.
     *
     * @var string
     */
    protected $source;

    /**
     * Destination file.
     *
     * @var string
     */
    protected $destination;

    /**
     * @var \Robo\Task\Filesystem\FilesystemStack
     */
    protected $filesystem;

    /**
     * @var \Robo\Task\File\Replace
     */
    protected $replace;

    /**
     * Constructs a new ProcessConfigFile task.
     *
     * @param string $source
     * @param string $destination
     */
    public function __construct($source, $destination)
    {
        $this->source = $source;
        $this->destination = $destination;
        $this->filesystem = new FilesystemStack();
        $this->replace = new Replace($destination);
    }

    /**
     * @return \Robo\Result
     * @throws \Robo\Exception\TaskException
     */
    public function run()
    {
        if (!file_exists($this->source)) {
            throw new TaskException($this, "Source file '{$this->source}' does not exists.");
        }

        $content = file_get_contents($this->source);
        $tokens = $this->extractProcessedTokens($content);

        $tasks = [];
        if ($this->source !== $this->destination) {
            $tasks[] = $this->filesystem->copy($this->source, $this->destination, true);
        }
        $tasks[] = $this->replace->from(array_keys($tokens))->to(array_values($tokens));

        return $this->collectionBuilder()->addTaskList($tasks)->run();
    }
}
