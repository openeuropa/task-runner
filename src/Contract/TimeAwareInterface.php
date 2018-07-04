<?php

namespace OpenEuropa\TaskRunner\Contract;

use OpenEuropa\TaskRunner\Services\Time;

/**
 * Interface TimeAwareInterface
 *
 * @package OpenEuropa\TaskRunner\Contract
 */
interface TimeAwareInterface
{
    /**
     * @return $this
     */
    public function getTime();

    /**
     * @param \OpenEuropa\TaskRunner\Services\Time $time
     *
     * @return $this
     */
    public function setTime(Time $time);
}
