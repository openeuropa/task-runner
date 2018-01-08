<?php

namespace EC\My\Custom\TaskRunner\Commands;

use EC\OpenEuropa\TaskRunner\Commands\BaseCommands;

/**
 * Class SecondCustomCommands
 *
 * @package My\Custom\TaskRunner\Commands
 */
class SecondCustomCommands extends BaseCommands
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
