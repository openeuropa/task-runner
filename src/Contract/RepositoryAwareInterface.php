<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Contract;

use Gitonomy\Git\Repository;

/**
 * Interface for classes that interact with git repositories.
 */
interface RepositoryAwareInterface
{

    /**
     * @return Repository
     */
    public function getRepository();

    /**
     * @param \Gitonomy\Git\Repository $repository
     *
     * @return $this
     */
    public function setRepository(Repository $repository);
}
