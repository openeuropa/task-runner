<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tasks\CollectionFactory;

/**
 * Robo loadTasks trait for the CollectionFactory.
 *
 * phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
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
