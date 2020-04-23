<?php

namespace OpenEuropa\TaskRunner\Commands;

use Symfony\Component\Yaml\Yaml;

/**
 * Runner commands.
 */
class RunnerCommands extends AbstractCommands
{
    /**
     * Displays the current configuration
     *
     * @command config
     */
    public function config()
    {
        return Yaml::dump($this->getConfig()->export(), 10, 2);
    }
}
