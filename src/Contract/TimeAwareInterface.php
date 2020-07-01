<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Contract;

use OpenEuropa\TaskRunner\Services\Time;

/**
 * Interface for classes that need to know the system time.
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
