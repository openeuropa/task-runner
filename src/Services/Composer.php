<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Services;

/**
 * Parse composer package information.
 */
class Composer
{
    /**
     * @var string
     */
    private $workingDir;

    /**
     * Constructs a new Composer object.
     *
     * @param string $workingDir
     */
    public function __construct($workingDir)
    {
        $this->workingDir = $workingDir;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->hasName() ? $this->getPackage()->name : '';
    }

    /**
     * @return string
     */
    public function getVendor()
    {
        return $this->hasName() ? explode('/', $this->getName())[0] : '';
    }

    /**
     * @return string
     */
    public function getProject()
    {
        return $this->hasName() ? explode('/', $this->getName())[1] : '';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->hasType() ? $this->getPackage()->type : '';
    }

    /**
     * @return bool
     */
    public function hasName()
    {
        return isset($this->getPackage()->name);
    }

    /**
     * @return bool
     */
    public function hasType()
    {
        return isset($this->getPackage()->type);
    }

    /**
     * @return mixed
     */
    public function getPackage()
    {
        return json_decode(file_get_contents($this->workingDir . '/composer.json'));
    }

    /**
     * @param string $workingDir
     *
     * @return Composer
     */
    public function setWorkingDir($workingDir)
    {
        $this->workingDir = $workingDir;

        return $this;
    }
}
