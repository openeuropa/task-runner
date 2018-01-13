<?php

namespace EC\OpenEuropa\TaskRunner\Traits;

/**
 * Trait FilesystemAwareTrait
 *
 * @package EC\OpenEuropa\TaskRunner\Traits
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
