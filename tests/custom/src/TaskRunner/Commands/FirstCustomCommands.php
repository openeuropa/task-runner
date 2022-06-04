<?php

declare(strict_types=1);

namespace My\Custom\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;

/**
 * Test commands.
 */
class FirstCustomCommands extends AbstractCommands
{
    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile(): string
    {
        return __DIR__ . '/config/config.yml';
    }

    /**
     * @command custom:command-one
     */
    public function commandOne()
    {
    }

    /**
     * @command custom:command-two
     */
    public function commandTwo()
    {
    }
}
