<?php

namespace EC\OpenEuropa\TaskRunner\Traits;

use EC\OpenEuropa\TaskRunner\Services\Composer;

/**
 * Trait ComposerAwareTrait
 *
 * @package EC\OpenEuropa\TaskRunner\Traits
 */
trait ComposerAwareTrait
{
    /**
     * @var \EC\OpenEuropa\TaskRunner\Services\Composer
     */
    protected $composer;

    /**
     * @return \EC\OpenEuropa\TaskRunner\Services\Composer
     */
    public function getComposer()
    {
        return $this->composer;
    }

    /**
     * @param \EC\OpenEuropa\TaskRunner\Services\Composer $composer
     *
     * @return $this
     */
    public function setComposer(Composer $composer)
    {
        $this->composer = $composer;

        return $this;
    }
}
