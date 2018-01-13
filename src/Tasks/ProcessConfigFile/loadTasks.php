<?php

namespace EC\OpenEuropa\TaskRunner\Tasks\ProcessConfigFile;

/**
 * Trait loadTasks
 *
 * @package EC\OpenEuropa\TaskRunner\Tasks\ProcessConfigFile
 */
trait loadTasks
{
    /**
     * @param $source
     * @param $destination
     *
     * @return \EC\OpenEuropa\TaskRunner\Tasks\ProcessConfigFile\ProcessConfigFile
     */
    public function taskProcessConfigFile($source, $destination)
    {
        return $this->task(ProcessConfigFile::class, $source, $destination);
    }
}
