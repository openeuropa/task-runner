<?php

namespace OpenEuropa\TaskRunner\Contract;

use Gitonomy\Git\Repository;

/**
 * Interface RepositoryAwareInterface
 *
 * @package OpenEuropa\TaskRunner\Contract
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
