<?php

namespace EC\My\Custom\TaskRunner\Commands;

use EC\OpenEuropa\TaskRunner\Commands\AbstractCommands;

/**
 * Class SecondCustomCommands
 *
 * @package My\Custom\TaskRunner\Commands
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
