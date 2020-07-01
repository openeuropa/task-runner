<?php

declare(strict_types=1);

namespace My\Custom\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;

/**
 * Test commands.
 */
class SecondCustomCommands extends AbstractCommands
{
    /**
     * @command custom:command-three
     */
    public function commandThree()
    {
    }

    /**
     * @command custom:command-four
     */
    public function commandFour()
    {
    }
}
