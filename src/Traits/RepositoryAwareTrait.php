<?php

namespace OpenEuropa\TaskRunner\Traits;

use Gitonomy\Git\Repository;

/**
 * Trait RepositoryAwareTrait
 *
 * @package OpenEuropa\TaskRunner\Traits
 */
trait RepositoryAwareTrait
{
    /**
     * @var \Gitonomy\Git\Repository
     */
    protected $repository;

    /**
     * @return \Gitonomy\Git\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param \Gitonomy\Git\Repository $repository
     *
     * @return $this
     */
    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;

        return $this;
    }
}
