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
     * @param array $options
     *
     * @return \OpenEuropa\TaskRunner\Tasks\CollectionFactory\CollectionFactory
     */
    public function taskCollectionFactory(array $tasks, array $options = [])
    {
        return $this->task(CollectionFactory::class, $tasks, $options);
    }
}
