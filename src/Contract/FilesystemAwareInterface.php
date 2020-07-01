<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Contract;

/**
 * Interface for classes that have to interact with the filesystem.
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
