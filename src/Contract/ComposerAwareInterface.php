<?php

namespace EC\OpenEuropa\TaskRunner\Contract;

use EC\OpenEuropa\TaskRunner\Services\Composer;

/**
 * Interface ComposerAwareInterface
 *
 * @package EC\OpenEuropa\TaskRunner\Contract
 */
interface ComposerAwareInterface
{
    /**
     * @return \EC\OpenEuropa\TaskRunner\Services\Composer
     */
    public function getComposer();

    /**
     * @param \EC\OpenEuropa\TaskRunner\Services\Composer $composer
     *
     * @return $this
     */
    public function setComposer(Composer $composer);
}
