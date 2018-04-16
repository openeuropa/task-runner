<?php

namespace OpenEuropa\TaskRunner\Traits;

/**
 * Trait TimeAwareTrait
 *
 * @package OpenEuropa\TaskRunner\Traits
 */
trait TimeAwareTrait
{
    /**
     * @var \OpenEuropa\TaskRunner\Services\Time
     */
    protected $time;

    /**
     * @return \OpenEuropa\TaskRunner\Services\Time
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param \OpenEuropa\TaskRunner\Services\Time $time
     *
     * @return TimeAwareTrait
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }
}
