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
