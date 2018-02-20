<?php

namespace OpenEuropa\TaskRunner\Tasks\CollectionFactory;

/**
 * Trait loadTasks
 *
 * @package OpenEuropa\TaskRunner\Tasks\CollectionFactory
 */
trait loadTasks
{
    /**
     * @param array $tasks
     *
     * @return \OpenEuropa\TaskRunner\Tasks\CollectionFactory\CollectionFactory
     */
    public function taskCollectionFactory(array $tasks)
    {
        return $this->task(CollectionFactory::class, $tasks);
    }
}
