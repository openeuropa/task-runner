<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Contract;

use OpenEuropa\TaskRunner\Services\Composer;

/**
 * Interface for classes that interact with the composer manifest.
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
