<?php

namespace OpenEuropa\TaskRunner\Contract;

/**
 * Interface FilesystemAwareTrait
 *
 * @package OpenEuropa\TaskRunner\Contract
 */
interface FilesystemAwareInterface
{
    /**
     * @return \Symfony\Component\Filesystem\Filesystem
     */
    public function getFilesystem();

    /**
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     *
     * @return $this
     */
    public function setFilesystem($filesystem);
}
