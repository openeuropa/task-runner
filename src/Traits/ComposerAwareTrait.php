<?php

namespace OpenEuropa\TaskRunner\Traits;

use OpenEuropa\TaskRunner\Services\Composer;

/**
 * Trait ComposerAwareTrait
 *
 * @package OpenEuropa\TaskRunner\Traits
 */
trait ComposerAwareTrait
{
    /**
     * @var \OpenEuropa\TaskRunner\Services\Composer
     */
    protected $composer;

    /**
     * @return \OpenEuropa\TaskRunner\Services\Composer
     */
    public function getComposer()
    {
        return $this->composer;
    }

    /**
     * @param \OpenEuropa\TaskRunner\Services\Composer $composer
     *
     * @return $this
     */
    public function setComposer(Composer $composer)
    {
        $this->composer = $composer;

        return $this;
    }
}
