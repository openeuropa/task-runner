<?php

namespace EC\OpenEuropa\TaskRunner\Services;

/**
 * Parse composer package information.
 *
 * @package EC\OpenEuropa\TaskRunner\Services
 */
class Composer
{
    /**
     * Get project name from composer.json.
     *
     * @return string
     *   Project name.
     */
    public function getFullProjectName()
    {
        $package = json_decode(file_get_contents('./composer.json'));

        return $package->name;
    }
}
