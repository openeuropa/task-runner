<?php

namespace EC\OpenEuropa\TaskRunner\Tasks\CollectionFactory;

/**
 * Trait loadTasks
 *
 * @package EC\OpenEuropa\TaskRunner\Tasks\CollectionFactory
 */
trait loadTasks
{
    /**
     * @param array $tasks
     *
     * @return \EC\OpenEuropa\TaskRunner\Tasks\CollectionFactory\CollectionFactory
     */
    public function taskCollectionFactory(array $tasks)
    {
        return $this->task(CollectionFactory::class, $tasks);
    }
}
