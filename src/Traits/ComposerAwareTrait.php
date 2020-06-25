<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Traits;

use OpenEuropa\TaskRunner\Services\Composer;

/**
 * Trait ComposerAwareTrait
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
