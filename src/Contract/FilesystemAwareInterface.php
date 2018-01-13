<?php

namespace EC\OpenEuropa\TaskRunner\Contract;

/**
 * Interface FilesystemAwareTrait
 *
 * @package EC\OpenEuropa\TaskRunner\Contract
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
