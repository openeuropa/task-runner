<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Commands;

use Robo\Exception\AbortTasksException;
use Symfony\Component\Yaml\Yaml;

/**
 * Commands to interact with the task runner itself.
 */
class RunnerCommands extends AbstractCommands
{

    /**
     * Displays the current configuration in YAML format.
     *
     * If no argument is passed the whole configuration is outputted. To display
     * a specific configuration, pass the key as argument (e.g. `drupal.root`).
     *
     * @command config
     *
     * @param string|null $key Optional configuration key
     * @return string
     * @throws \Robo\Exception\AbortTasksException
     *
     * @todo Implement a `--format` option to allow different output formats.
     */
    public function config(?string $key = null): string
    {
        if (!$key) {
            $config = $this->getConfig()->export();
        } else {
            if (!$this->getConfig()->has($key)) {
                throw new AbortTasksException("Invalid '$key' key.");
            }
            $config = $this->getConfig()->get($key);
        }
        return trim(Yaml::dump($config, 10, 2));
    }
}
