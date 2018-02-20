<?php

namespace OpenEuropa\TaskRunner\Tasks\ProcessConfigFile;

/**
 * Trait loadTasks
 *
 * @package OpenEuropa\TaskRunner\Tasks\ProcessConfigFile
 */
trait loadTasks
{
    /**
     * @param $source
     * @param $destination
     *
     * @return \OpenEuropa\TaskRunner\Tasks\ProcessConfigFile\ProcessConfigFile
     */
    public function taskProcessConfigFile($source, $destination)
    {
        return $this->task(ProcessConfigFile::class, $source, $destination);
    }
}
