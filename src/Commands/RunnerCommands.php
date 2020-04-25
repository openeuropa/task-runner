<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Commands;

use Robo\Exception\AbortTasksException;
use Symfony\Component\Yaml\Yaml;

/**
 * Runner commands.
 */
class RunnerCommands extends AbstractCommands
{

    /**
     * Displays the current configuration with YAML representation.
     *
     * If no argument is passed the whole configuration is outputted. To display
     * a specific configuration, pass the key as argument (e.g. `drupal.root`).
     *
     * @command config
     *
     * @param string|null $key
     * @return string
     * @throws \Robo\Exception\AbortTasksException
     *
     * @todo Implement a `--format` option to allow formatted output.
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
