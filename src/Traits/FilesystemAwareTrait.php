<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Traits;

/**
 * Trait FilesystemAwareTrait
 */
trait FilesystemAwareTrait
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @return \Symfony\Component\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     *
     * @return FilesystemAwareTrait
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }
}
