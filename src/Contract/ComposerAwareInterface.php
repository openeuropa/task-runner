<?php

namespace OpenEuropa\TaskRunner\Contract;

use OpenEuropa\TaskRunner\Services\Composer;

/**
 * Interface ComposerAwareInterface
 *
 * @package OpenEuropa\TaskRunner\Contract
 */
interface ComposerAwareInterface
{
    /**
     * @return \OpenEuropa\TaskRunner\Services\Composer
     */
    public function getComposer();

    /**
     * @param \OpenEuropa\TaskRunner\Services\Composer $composer
     *
     * @return $this
     */
    public function setComposer(Composer $composer);
}
