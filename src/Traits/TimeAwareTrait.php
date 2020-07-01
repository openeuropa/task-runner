<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Traits;

use OpenEuropa\TaskRunner\Services\Time;

/**
 * Trait TimeAwareTrait
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
    public function setTime(Time $time)
    {
        $this->time = $time;

        return $this;
    }
}
