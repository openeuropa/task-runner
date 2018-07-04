<?php

namespace OpenEuropa\TaskRunner\Services;

/**
 * Class Time
 *
 * @package OpenEuropa\TaskRunner\Services
 */
class Time
{
    /**
     * @return int
     */
    public function getTimestamp()
    {
        return time();
    }
}
