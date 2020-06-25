<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tasks\ProcessConfigFile;

/**
 * Robo loadTasks trait for the config processing tasks.
 *
 * phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
 */
trait loadTasks
{
    /**
     * Replaces placeholders with actual values.
     *
     * @param string $source
     *   The path to the file to process.
     * @param string $destination
     *   The path where to store the processed file.
     *
     * @return \OpenEuropa\TaskRunner\Tasks\ProcessConfigFile\ProcessConfigFile
     */
    public function taskProcessConfigFile($source, $destination)
    {
        return $this->task(ProcessConfigFile::class, $source, $destination);
    }
}
