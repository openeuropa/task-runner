<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Services;

/**
 * Showcases how to use a built-in PHP function in a more complicated way.
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
