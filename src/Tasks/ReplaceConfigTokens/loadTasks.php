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
     * @param $source
     * @param $destination
     *
     * @return \EC\OpenEuropa\TaskRunner\Tasks\ReplaceConfigTokens\ReplaceConfigTokens
     */
    public function taskReplaceConfigTokens($source, $destination)
    {
        return $this->task(ReplaceConfigTokens::class, $source, $destination);
    }
}
