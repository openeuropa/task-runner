<?php

namespace EC\OpenEuropa\TaskRunner\Tasks\ReplaceConfigTokens;

/**
 * Trait loadTasks
 *
 * @package EC\OpenEuropa\TaskRunner\Tasks\ReplaceConfigTokens
 */
trait loadTasks
{

    /**
     * @return \EC\OpenEuropa\TaskRunner\Tasks\ReplaceConfigTokens\ReplaceConfigTokens
     */
    public function taskReplaceConfigTokens()
    {
        return $this->task(ReplaceConfigTokens::class);
    }
}
